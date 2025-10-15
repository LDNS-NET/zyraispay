<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Include MikroTik API routes
require __DIR__.'/api-mikrotik.php';

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('health', function () {
        return response()->json(['status' => 'ok']);
    });
    
    // MikroTik authentication endpoints
    Route::prefix('mikrotik')->group(function () {
        Route::post('auth', [\App\Http\Controllers\Api\MikrotikAuthController::class, 'authenticate']);
        Route::get('profile', [\App\Http\Controllers\Api\MikrotikAuthController::class, 'getProfile']);
    });
});

// Protected API routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // User details (default Laravel endpoint)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Add more protected routes here
});
