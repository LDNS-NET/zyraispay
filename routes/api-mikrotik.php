<?php

use App\Http\Controllers\Api\MikrotikAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MikroTik API Routes
|--------------------------------------------------------------------------
|
| These routes are used by MikroTik routers to authenticate users and fetch
| their profiles. These endpoints are stateless and use API tokens for auth.
|
*/

Route::prefix('mikrotik')->group(function () {
    // Authenticate a user (called by MikroTik when user tries to connect)
    Route::post('auth', [MikrotikAuthController::class, 'authenticate']);
    
    // Get user profile (can be used by MikroTik to verify user details)
    Route::get('profile', [MikrotikAuthController::class, 'getProfile']);
});
