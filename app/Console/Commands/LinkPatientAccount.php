<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Console\Command;

class LinkPatientAccount extends Command
{
    protected $signature = 'patient:link {user_email} {patient_id?}';
    protected $description = 'Link a patient user account to a clinic patient record';

    public function handle()
    {
        $user = User::where('email', $this->argument('user_email'))->first();
        if (! $user) {
            $this->error("No user with email {$this->argument('user_email')}");
            return self::FAILURE;
        }
        if ($user->role !== 'patient') {
            $this->error("User {$user->email} has role '{$user->role}' — not a patient.");
            return self::FAILURE;
        }

        $patientId = $this->argument('patient_id');
        if ($patientId) {
            $patient = Patient::find($patientId);
            if (! $patient) {
                $this->error("No patient with id {$patientId}");
                return self::FAILURE;
            }
        } else {
            $patient = Patient::where('email', $user->email)
                ->whereNull('user_id')
                ->first();
            if (! $patient) {
                $this->error("No unlinked patient with email {$user->email}. Try: php artisan patient:link {$user->email} <patient_id>");
                return self::FAILURE;
            }
        }

        $patient->user_id = $user->id;
        $patient->save();

        $this->info("Linked {$user->email} → patient #{$patient->id} ({$patient->name})");
        return self::SUCCESS;
    }
}
