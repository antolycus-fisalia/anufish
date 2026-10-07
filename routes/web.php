<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// User harus login untuk mengakses halaman di bawah ini
Route::middleware('auth')->group(function () {
    Route::view('/', 'pages.index')
        ->name('homepage');
//
//    Route::get('/profile', [ProfileController::class, 'show'])
//        ->name('profile.show');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/admin', function (Request $request) {
        abort_unless($request->user()->role === 'admin', 403);

        return 'Dashboard Admin';
    })->name('admin.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::view('/login', 'pages.auth.login')
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::view('/register', 'pages.auth.register')
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.store');
});

// Hanya untuk preview Profile UI di local development
if (app()->isLocal()) {
    Route::get('/dev/profile', function () {
        $user = User::firstOrFail();

        Auth::login($user);

        return redirect()->route('profile');
    });
}
