<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('web');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {

    Route::resource('users', UserController::class);
    Route::resource('items', ItemController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('dispatches', DispatchController::class);

    Route::get('dispatches/export/json', [DispatchController::class, 'exportMonthlyJson']);
    Route::get('dispatches/export/csv', [DispatchController::class, 'exportMonthlyCsv']);
});
