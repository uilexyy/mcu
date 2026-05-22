<?php

use App\Http\Controllers\Api\Admin\PackageController;
use App\Http\Controllers\Api\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Dokter\PhysicalExamController;
use App\Http\Controllers\Api\Karyawan\RegistrationController;
use App\Http\Controllers\Api\Lab\LabResultController;
use App\Http\Controllers\Api\Radiologi\RadiologiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Profile (any authenticated user)
    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/signature', [ProfileController::class, 'uploadSignature']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
    });

    // Karyawan
    Route::middleware(['auth:sanctum', 'role:karyawan'])->prefix('karyawan')->group(function () {
        Route::get('/registrations', [RegistrationController::class, 'index']);
        Route::post('/registrations', [RegistrationController::class, 'store']);
        Route::get('/registrations/{id}/download', [RegistrationController::class, 'download']);
    });

    // Public packages listing (any authenticated user)
    Route::middleware('auth:sanctum')->get('/packages', [PackageController::class, 'index']);

    // Registration detail (any staff role)
    Route::middleware(['auth:sanctum'])->get('/registrations/{id}', [AdminRegistrationController::class, 'show']);

    // Admin
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('/registrations', [AdminRegistrationController::class, 'index']);
        Route::get('/export/registrations', [AdminRegistrationController::class, 'export']);
        Route::put('/registrations/{id}/approve', [AdminRegistrationController::class, 'approve']);
        Route::put('/registrations/{id}/reject', [AdminRegistrationController::class, 'reject']);
        Route::get('/registrations/{id}/download', [AdminRegistrationController::class, 'download']);
        Route::get('/stats', [AdminRegistrationController::class, 'stats']);
        Route::get('/activity-logs', [AdminRegistrationController::class, 'logs']);

        Route::apiResource('/packages', PackageController::class);
        Route::apiResource('/users', UserController::class);
        Route::post('/users/{user}/signature', [UserController::class, 'uploadSignature']);
    });

    // Dokter Umum
    Route::middleware(['auth:sanctum', 'role:dokter_umum'])->prefix('dokter')->group(function () {
        Route::get('/queue', [PhysicalExamController::class, 'queue']);
        Route::post('/physical-exam', [PhysicalExamController::class, 'store']);
        Route::put('/physical-exam/{id}', [PhysicalExamController::class, 'update']);
        Route::get('/riwayat', [PhysicalExamController::class, 'riwayat']);
        Route::get('/registrations/{id}/history', [PhysicalExamController::class, 'history']);
    });

    // Laboratorium
    Route::middleware(['auth:sanctum', 'role:laboratorium'])->prefix('lab')->group(function () {
        Route::get('/queue', [LabResultController::class, 'queue']);
        Route::get('/riwayat', [LabResultController::class, 'riwayat']);
        Route::post('/results', [LabResultController::class, 'store']);
        Route::put('/results/{id}', [LabResultController::class, 'update']);
        Route::get('/registrations/{id}/history', [LabResultController::class, 'history']);
    });

    // Radiologi
    Route::middleware(['auth:sanctum', 'role:radiologi'])->prefix('radiologi')->group(function () {
        Route::get('/queue', [RadiologiController::class, 'queue']);
        Route::get('/riwayat', [RadiologiController::class, 'riwayat']);
        Route::post('/results', [RadiologiController::class, 'store']);
        Route::put('/results/{id}', [RadiologiController::class, 'update']);
        Route::get('/registrations/{id}/history', [RadiologiController::class, 'history']);
    });
});
