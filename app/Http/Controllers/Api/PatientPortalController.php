<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientPortalController extends Controller
{
    private function ensurePatient(Request $request): void
    {
        if ($request->user()->role !== 'patient') {
            abort(403, 'Only patient accounts can access the client portal.');
        }
    }

    public function myAppointment(Request $request)
    {
        $this->ensurePatient($request);

        $patient = Patient::where('user_id', $request->user()->id)->first();

        if (! $patient) {
            return response()->json([
                'linked' => false,
                'message' => 'Your account is not yet linked to a clinic record. Please contact your health facility to complete the link.',
            ]);
        }

        return response()->json([
            'linked' => true,
            'patient' => $patient,
        ]);
    }

    public function confirmAttendance(Request $request)
    {
        $this->ensurePatient($request);

        $patient = Patient::where('user_id', $request->user()->id)->first();

        if (! $patient) {
            return response()->json(['error' => 'No linked patient record'], 404);
        }

        if (! $patient->next_appointment_date) {
            return response()->json(['error' => 'No upcoming appointment to confirm'], 400);
        }

        $patient->patient_confirmed_at = now();
        $patient->save();

        return response()->json([
            'message' => 'Your attendance has been confirmed. Thank you!',
            'patient' => $patient,
        ]);
    }
}
