<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// User harus login untuk mengakses halaman di bawah ini
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    })->name('homepage');
});

// Hanya untuk user yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('pages.auth.register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.store');
});
