<?php

use App\Core\Auth\Controllers\LoginController;
use App\Core\Auth\Controllers\LogoutController;
use App\Core\Auth\Controllers\RegisterController;
use App\Core\Auth\Controllers\ResetPasswordController;
use App\Core\Auth\Controllers\SendPasswordResetLinkController;
use App\Core\Auth\Controllers\ShowForgotPasswordController;
use App\Core\Auth\Controllers\ShowLoginController;
use App\Core\Auth\Controllers\ShowRegisterController;
use App\Core\Auth\Controllers\ShowResetPasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core\Auth Routes
|--------------------------------------------------------------------------
| Guest-facing auth endpoints. Loaded by routes/penova.php under the
| plain "web" middleware group (NOT the admin prefix).
| One invokable controller per action (the panel-wide convention).
*/

Route::middleware('guest')->group(function () {
    Route::get('login', ShowLoginController::class)->name('login');
    Route::post('login', LoginController::class)->name('login.store');

    Route::get('forgot-password', ShowForgotPasswordController::class)->name('password.request');
    Route::post('forgot-password', SendPasswordResetLinkController::class)->name('password.email');

    Route::get('reset-password/{token}', ShowResetPasswordController::class)->name('password.reset');
    Route::post('reset-password', ResetPasswordController::class)->name('password.update');

    // Self-registration is opt-in per product (PENOVA_REGISTRATION=true).
    // Note: evaluated at boot — rebuild the route cache (route:cache)
    // after toggling the flag in production.
    if (config('penova.auth.registration')) {
        Route::get('register', ShowRegisterController::class)->name('register');
        Route::post('register', RegisterController::class)->name('register.store');
    }
});

Route::post('logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');
