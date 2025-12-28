<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DashboardController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::apiResource('regions', RegionController::class);

Route::middleware('auth:sanctum, verified')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Route::middleware('admin')->group(function () {
    //     Route::get('/users', [UserController::class, 'index']);
    //     Route::get('/users/{id}', [UserController::class, 'show']);
    //     Route::post('/users', [UserController::class, 'store']);
    //     Route::patch('/users/{id}', [UserController::class, 'update']);
    //     Route::delete('/users/{id}', [UserController::class, 'destroy']);
    // });

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('assets', AssetController::class);
    Route::apiResource('assignments', AssignmentController::class);
    Route::apiResource('reports', ReportController::class);

});
