<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Patient\DailyLogController;
use App\Http\Controllers\Patient\ReportController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rute Tamu (Hanya dapat diakses jika belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

// Rute Pengguna Terotentikasi (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dispatcher dashboard sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ruang Chat Konsultasi Anonim Dua Arah (Pasien & Mahasiswa Psikologi)
    Route::get('/consultations/{id}', [ConsultationController::class, 'show'])->name('consultations.show');
    Route::post('/consultations/{id}/messages', [ConsultationController::class, 'sendMessage'])->name('consultations.messages.store');
    Route::patch('/consultations/{id}/status', [ConsultationController::class, 'updateStatus'])->name('consultations.status.update');

    // Dashboard & Fitur Khusus Penderita (Patient)
    Route::middleware('role:patient')->prefix('patient')->name('patient.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'patient'])->name('dashboard');

        // CRUD Input Harian (Multi-Mood, Siklus Tidur, Multi-Resep & Log Obat)
        Route::post('/mood', [DailyLogController::class, 'storeMood'])->name('mood.store');
        Route::delete('/mood/{id}', [DailyLogController::class, 'destroyMood'])->name('mood.destroy');

        Route::post('/sleep', [DailyLogController::class, 'storeSleep'])->name('sleep.store');

        Route::post('/prescriptions', [DailyLogController::class, 'storePrescription'])->name('prescriptions.store');
        Route::delete('/prescriptions/{id}', [DailyLogController::class, 'destroyPrescription'])->name('prescriptions.destroy');

        Route::post('/medication', [DailyLogController::class, 'storeMedication'])->name('medication.store');
        Route::delete('/medication/{id}', [DailyLogController::class, 'destroyMedication'])->name('medication.destroy');

        // Muat data sampel klinis 14 hari pasien bipolar asli
        Route::post('/seed-sample', [DailyLogController::class, 'seedClinicalSample'])->name('seed-sample');

        // Laporan Grafik 2 Minggu & Export PDF
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');

        // Konsultasi Anonim dengan Mahasiswa Psikologi (DEMO)
        Route::get('/consultations', [ConsultationController::class, 'indexPatient'])->name('consultations');
        Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
    });

    // Dashboard Khusus Mahasiswa Psikologi (Counselor - DEMO)
    Route::middleware('role:counselor')->prefix('counselor')->name('counselor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'counselor'])->name('dashboard');
    });

    // Dashboard Khusus Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::post('/recommendations', [DashboardController::class, 'storeRecommendation'])->name('recommendations.store');
        Route::delete('/recommendations/{id}', [DashboardController::class, 'destroyRecommendation'])->name('recommendations.destroy');
    });
});
