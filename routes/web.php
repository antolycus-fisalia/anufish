<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    })->name('homepage');

    Route::get('/admin', function (Request $request) {
        abort_unless($request->user()->role === 'admin', 403);

        return 'Dashboard Admin';
    })->name('admin.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', function () {
        return view('pages.auth.register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.store');
});
