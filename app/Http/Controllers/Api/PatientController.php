<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PatientController extends Controller
{
    public function index()
    {
        return Patient::orderByDesc('id')->get();
    }

    public function unlinkedUsers()
    {
        return User::where('role', 'patient')
            ->whereDoesntHave('patientProfile')
            ->get(['id', 'name', 'email', 'created_at']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|in:M,F',
            'email' => 'nullable|email|max:255',
            'distance_km' => 'required|numeric|min:0',
            'previous_missed_count' => 'required|integer|min:0',
            'avg_delay_days' => 'required|numeric|min:0',
            'next_appointment_date' => 'nullable|date',
        ]);

        $risk = $this->predictRisk($validated);

        $userId = null;
        if (! empty($validated['email'])) {
            $user = User::where('email', $validated['email'])->where('role', 'patient')->first();
            if ($user) $userId = $user->id;
        }

        $patient = Patient::create(array_merge($validated, [
            'user_id' => $userId,
            'risk_score' => $risk['risk_probability'] ?? null,
            'risk_tier' => $risk['risk_tier'] ?? 'Unknown',
        ]));

        return response()->json($patient, 201);
    }

    public function show(Patient $patient) { return $patient; }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:0|max:120',
            'gender' => 'sometimes|required|in:M,F',
            'email' => 'sometimes|nullable|email|max:255',
            'distance_km' => 'sometimes|required|numeric|min:0',
            'previous_missed_count' => 'sometimes|required|integer|min:0',
            'avg_delay_days' => 'sometimes|required|numeric|min:0',
            'next_appointment_date' => 'nullable|date',
        ]);

        // Merge new data with existing patient data for the ML prediction
        $mlPayload = [
            'age' => $validated['age'] ?? $patient->age,
            'distance_km' => $validated['distance_km'] ?? $patient->distance_km,
            'previous_missed_count' => $validated['previous_missed_count'] ?? $patient->previous_missed_count,
            'avg_delay_days' => $validated['avg_delay_days'] ?? $patient->avg_delay_days,
            'gender_F' => ($validated['gender'] ?? $patient->gender) === 'F' ? 1 : 0,
        ];

        $risk = $this->predictRisk($mlPayload);
        if ($risk['risk_probability'] !== null) {
            $validated['risk_score'] = $risk['risk_probability'];
            $validated['risk_tier'] = $risk['risk_tier'];
        }

        $patient->update($validated);
        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['message' => 'Patient deleted']);
    }

    private function predictRisk(array $data)
    {
        try {
            $response = Http::timeout(10)->acceptJson()->post('http://127.0.0.1:8000/predict', $data);
            if ($response->successful()) return $response->json();
        } catch (\Throwable $e) {}
        return ['risk_probability' => null, 'risk_tier' => 'Unknown'];
    }
}
