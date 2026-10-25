<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send reminders to patients with upcoming VL appointments';

    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $threshold = Carbon::now()->addDays(3)->toDateString();

        $patients = Patient::whereNotNull('next_appointment_date')
            ->where('next_appointment_date', '>=', $today)
            ->where('next_appointment_date', '<=', $threshold)
            ->orderBy('next_appointment_date')
            ->get();

        if ($patients->isEmpty()) {
            $this->info('No upcoming appointments in the next 3 days.');
            return self::SUCCESS;
        }

        foreach ($patients as $p) {
            $prefix = match ($p->risk_tier) {
                'High'   => '[URGENT]',
                'Medium' => '[NOTICE]',
                default  => '[REMINDER]',
            };
            $this->info("{$prefix} {$p->name} — VL test on {$p->next_appointment_date} (Risk: {$p->risk_tier})");
            Log::info("VL Reminder dispatched: {$p->name} (risk: {$p->risk_tier}) on {$p->next_appointment_date}");
        }

        $this->info('Total reminders dispatched: ' . $patients->count());
        return self::SUCCESS;
    }
}
