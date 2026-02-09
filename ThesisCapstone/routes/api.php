<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PermitController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ServiceProviderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
// routes/api.php


Route::middleware('auth:sanctum')->get('/service-provider/dashboard', [ServiceProviderController::class, 'dashboard']);

// Get logged-in user info
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {

    // User businesses
    Route::get('/businesses', [BusinessController::class, 'index']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/permits', [PermitController::class, 'index']);
        Route::post('/permits', [PermitController::class, 'store']);
        Route::put('/permits/{permit}', [PermitController::class, 'update']);
        Route::delete('/permits/{permit}', [PermitController::class, 'destroy']);
    });
});
// routes/api.php
