<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// prefix

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'login']) // old store
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

Route::middleware(["jwt.from.cookie"])->group(function () {
    // Logout route
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout']) // old destroy
        ->name('logout');
});

// Refresh route
Route::post('/refresh', [AuthenticatedSessionController::class, 'refresh']) 
    ->name('refresh');

Route::post('/verify-token', [AuthenticatedSessionController::class, 'verifyAccessToken']) 
    ->name('verifyToken');
