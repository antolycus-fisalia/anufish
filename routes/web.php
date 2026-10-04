<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Halaman yang hanya bisa diakses setelah login
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    })->name('homepage');

    // Sementara untuk menguji redirect admin
    Route::get('/admin', function () {
        return 'Dashboard Admin';
    })->name('admin.dashboard');
});

// Halaman yang hanya bisa diakses sebelum login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');

    // Route::get('/register', function () {
    //     return view('auth.register');
    // })->name('register');
});
