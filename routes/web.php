<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ======================================================
// HALAMAN YANG MEMERLUKAN LOGIN
// ======================================================
Route::middleware('auth')->group(function () {

    // UC-05 — Beranda / Pencarian Ikan
    Route::get('/', function (Request $request) {
        $fishes = [];
        $pagination = null;
        $error = null;

        /*
        |--------------------------------------------------------------------------
        | Preview state UC-05
        |--------------------------------------------------------------------------
        | Hanya aktif pada APP_ENV=local.
        | Digunakan untuk mengecek card, pagination, dan error state
        | sebelum GbifService benar-benar dihubungkan.
        */
        if (app()->isLocal()) {
            $preview = $request->query('preview');

            if ($preview === 'results') {
                $fishes = [
                    [
                        'common_name' => 'Tuna Sirip Kuning',
                        'scientific_name' => 'Thunnus albacares',
                        'image' => null,
                    ],
                    [
                        'common_name' => 'Ikan Badut',
                        'scientific_name' => 'Amphiprion ocellaris',
                        'image' => null,
                    ],
                    [
                        'common_name' => 'Salmon Atlantik',
                        'scientific_name' => 'Salmo salar',
                        'image' => null,
                    ],
                    [
                        'common_name' => 'Ikan Kerapu',
                        'scientific_name' => 'Epinephelus',
                        'image' => null,
                    ],
                ];

                $pagination = [
                    'current_page' => 1,
                    'total_pages' => 3,
                    'has_previous' => false,
                    'previous_page' => null,
                    'has_next' => true,
                    'next_page' => 2,
                ];
            }

            if ($preview === 'error') {
                $error = 'Layanan GBIF sedang tidak dapat diakses.';
            }
        }

        return view('pages.index', [
            'fishes' => $fishes,
            'pagination' => $pagination,
            'error' => $error,
        ]);
    })->name('homepage');


    // UC-04 — Halaman Profil
    Route::get('/profile', function (Request $request) {
        return view('pages.profile.show', [
            'user' => $request->user(),
        ]);
    })->name('profile');


    // UC-04 — Update Profil
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');


    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    // Dashboard Admin
    Route::get('/admin', function (Request $request) {
        abort_unless($request->user()->role === 'admin', 403);

        return 'Dashboard Admin';
    })->name('admin.dashboard');
});


// ======================================================
// HALAMAN UNTUK USER YANG BELUM LOGIN
// ======================================================
Route::middleware('guest')->group(function () {

    // UC-02 — Login UI
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login');


    // Proses Login
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');


    // UC-01 — Register UI
    Route::get('/register', function () {
        return view('pages.auth.register');
    })->name('register');


    // Proses Registrasi
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.store');
});