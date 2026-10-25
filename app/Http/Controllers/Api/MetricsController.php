<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class MetricsController extends Controller
{
    public function index()
    {
        try {
            $response = Http::timeout(10)->get('http://127.0.0.1:8000/metrics');
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['error' => 'ML service returned an error'], 502);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'ML service unreachable',
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
