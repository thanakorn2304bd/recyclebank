<?php

use App\Http\Controllers\AccountPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegistrationStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('register/documents', [RegisteredUserController::class, 'documents'])
        ->name('register.documents');

    Route::post('register/documents', [RegisteredUserController::class, 'complete'])
        ->name('register.complete');

    Route::get('registration-status', [RegistrationStatusController::class, 'show'])
        ->name('registration-status.show');

    Route::patch('registration-status/documents', [RegistrationStatusController::class, 'resubmitDocuments'])
        ->name('registration-status.documents.update');

    Route::post('registration-status/logout', [RegistrationStatusController::class, 'clearAccess'])
        ->name('registration-status.logout');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('account/password', [AccountPasswordController::class, 'edit'])
        ->name('account.password.edit');
    Route::put('account/password', [AccountPasswordController::class, 'update'])
        ->name('account.password.update');
});
