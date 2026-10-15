<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/language', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('homepage');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'show')->name('show');
        Route::put('/', 'update')->name('update');
    });

    Route::prefix('fish')->name('fish.')->controller(FishController::class)
        ->where(['taxonId' => '[1-9][0-9]{0,9}'])->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/search', 'search')->name('search');
            Route::get('/taxa/{taxonId}', 'show')->name('show');
            Route::get('/taxa/{taxonId}/extras', 'extras')->name('extras');
            Route::get('/taxa/{taxonId}/photos', 'photos')->name('photos');
        });

    Route::get('/admin', [AdminController::class, 'index'])
        ->middleware('admin')->name('admin.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::view('/login', 'pages.auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')->name('login.store');

    Route::view('/register', 'pages.auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')->name('register.store');
});
