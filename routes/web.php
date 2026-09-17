<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [AboutController::class, 'index']);
Route::get('/feature', [FeaturesController::class, 'index']);