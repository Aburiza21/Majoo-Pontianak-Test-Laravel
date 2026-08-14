<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require JWT token)
Route::middleware('jwt.auth')->group(function () {
    Route::get('/report/monthly', [ReportController::class, 'monthly']);
});
