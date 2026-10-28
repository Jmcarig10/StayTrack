<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PasswordChangeRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordChangeRequestController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordChangeRequestController::class, 'store'])
        ->middleware('throttle:5,15')
        ->name('password.email');

    Route::get('password-reset-link', [PasswordResetLinkController::class, 'create'])
        ->name('password.link');

    Route::post('password-reset-link', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,15')
        ->name('password.link.send');

    Route::get('reset-password/{token}', [PasswordResetController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [PasswordResetController::class, 'store'])
        ->name('password.reset.store');
});

Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
