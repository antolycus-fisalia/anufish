<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterViewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/about', [AboutController::class, 'index']);

Route::get('/feature', [FeaturesController::class, 'index']);

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::view('/register', 'auth.register')->name('register');
});

Route::get('/profile', [ProfileController::class, 'index'])
    ->middleware('auth')
    ->name('profile.index');


Route::middleware('guest')->group(function () {

    Route::get('/register', [RegisterViewController::class, 'create'])
        ->name('register');


    /*
     * Route sementara.
     * Nanti hapus kalau backend ANU-38 sudah menyediakan register.store.
     */
    Route::post('/register', function (Request $request) {

        $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'min:8',
                'confirmed',
            ],
        ], [
            'nama.required' =>
                'Nama wajib diisi.',

            'email.required' =>
                'Email wajib diisi.',

            'email.email' =>
                'Format email tidak valid.',

            'password.required' =>
                'Password wajib diisi.',

            'password.min' =>
                'Password minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi password tidak sama.',
        ]);

        return back();

    })->name('register.store');


    /*
     * Sementara karena halaman login belum dikerjakan
     * pada branch feature/register-view.
     */
    Route::get('/login', function () {
        return 'Halaman login belum tersedia.';
    })->name('login');

});