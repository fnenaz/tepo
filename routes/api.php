<?php

use App\Http\Controllers\Api\TepoApiController;
use Illuminate\Support\Facades\Route;

// Autentikasi Publik API (Tanpa CSRF untuk pengujian Postman)
Route::post('/register', [TepoApiController::class, 'register']);
Route::post('/login', [TepoApiController::class, 'login']);

// Endpoint Terotentikasi (Mendukung Bearer Token / X-API-Token / Cookie Sesi Postman)
Route::middleware('api.auth')->group(function () {
    Route::get('/me', [TepoApiController::class, 'me']);
    Route::post('/logout', [TepoApiController::class, 'logout']);

    // Fitur Pasien
    Route::middleware('role:patient')->prefix('patient')->group(function () {
        Route::get('/dashboard', [TepoApiController::class, 'patientDashboard']);

        Route::post('/mood', [TepoApiController::class, 'storeMood']);
        Route::delete('/mood/{id}', [TepoApiController::class, 'destroyMood']);

        Route::post('/sleep', [TepoApiController::class, 'storeSleep']);

        Route::get('/prescriptions', [TepoApiController::class, 'listPrescriptions']);
        Route::post('/prescriptions', [TepoApiController::class, 'storePrescription']);
        Route::delete('/prescriptions/{id}', [TepoApiController::class, 'destroyPrescription']);

        Route::post('/medication', [TepoApiController::class, 'storeMedication']);
        Route::delete('/medication/{id}', [TepoApiController::class, 'destroyMedication']);

        Route::get('/reports', [TepoApiController::class, 'patientReports']);
    });

    // Konsultasi Anonim Dua Arah (Pasien & Mahasiswa Psikologi DEMO)
    Route::prefix('consultations')->group(function () {
        Route::get('/', [TepoApiController::class, 'listConsultations']);
        Route::post('/', [TepoApiController::class, 'storeConsultation'])->middleware('role:patient');
        Route::get('/{id}', [TepoApiController::class, 'showConsultation']);
        Route::post('/{id}/messages', [TepoApiController::class, 'sendConsultationMessage']);
        Route::patch('/{id}/status', [TepoApiController::class, 'updateConsultationStatus']);
    });

    // Fitur Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/recommendations', [TepoApiController::class, 'listRecommendations']);
        Route::post('/recommendations', [TepoApiController::class, 'storeRecommendation']);
        Route::delete('/recommendations/{id}', [TepoApiController::class, 'destroyRecommendation']);
    });
});
