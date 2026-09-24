<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterViewController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/about', [AboutController::class, 'index']);
Route::get('/tentang', [AboutController::class, 'index']);

Route::get('/feature', [FeaturesController::class, 'index']);
Route::get('/fitur', [FeaturesController::class, 'index']);

Route::get('/admin', function (): Response {
    return response()->noContent();
})->middleware(['auth', 'admin'])->name('admin.index');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [RegisterViewController::class, 'create'])->name('register');
    Route::post('/register', [RegisterViewController::class, 'store'])->middleware('throttle:registration')->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/profile', [ProfileController::class, 'index'])
    ->middleware('auth')
    ->name('profile.index');

Route::put('/profile', [ProfileController::class, 'update'])
    ->name('profile.update')
    ->middleware('auth');
