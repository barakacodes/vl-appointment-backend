<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PatientPortalController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Nurse / facility routes
    Route::get('/unlinked-users', [PatientController::class, 'unlinkedUsers']);
    Route::apiResource('patients', PatientController::class);
    Route::get('/model-metrics', [MetricsController::class, 'index']);

    // Client / patient routes
    Route::get('/my-appointment', [PatientPortalController::class, 'myAppointment']);
    Route::post('/my-appointment/confirm', [PatientPortalController::class, 'confirmAttendance']);
});
