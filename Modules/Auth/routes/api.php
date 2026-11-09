<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1/auth')->name('auth.')->group(function (): void {
    /*
     * Public endpoints.
     */

    // Rate limited: 5 login attempts per minute per mobile + IP.
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:auth-login')
        ->name('login');

    // Rate limited: 3 OTP requests per minute per IP.
    Route::post('otp/send', [AuthController::class, 'sendOtp'])
        ->middleware('throttle:auth-otp')
        ->name('otp.send');

    /*
     * Protected endpoints.
     * Require a valid JWT in the Authorization: Bearer header.
     */
    Route::middleware('auth:api')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
