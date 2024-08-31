<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::resource('items', ItemController::class);
Route::resource('staff', StaffController::class);
