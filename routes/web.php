<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// User harus login untuk mengakses halaman di bawah ini
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    })->name('homepage');

    Route::get('/profile', function () {
        return view('pages.profile.show', [
            'user' => Auth::user(),
        ]);
    })->name('profile');
});

// Hanya untuk user yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login');

    // Route::get('/register', function () {
    //     return view('pages.auth.register');
    // })->name('register');
});

// Hanya untuk preview Profile UI di local development
if (app()->isLocal()) {
    Route::get('/dev/profile', function () {
        $user = User::firstOrFail();

        Auth::login($user);

        return redirect()->route('profile');
    });
}