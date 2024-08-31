<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::resource('items', ItemController::class);
