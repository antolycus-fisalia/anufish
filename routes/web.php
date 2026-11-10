<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminUserEditController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ScanHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
*/

Route::post('/language', [LocaleController::class, 'update'])
    ->name('locale.update');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
|--------------------------------------------------------------------------
| Article Form Pages
|--------------------------------------------------------------------------
*/

    Route::prefix('artikel-saya')
    ->name('artikel-saya.')
    ->group(function () {
        Route::view('/buat', 'pages.article.form')
            ->name('create');

        Route::view('/{artikel}/edit', 'pages.article.form')
            ->whereNumber('artikel')
            ->name('edit');
    });

    // Homepage
    Route::get('/', HomeController::class)
        ->name('homepage');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Scan
    |--------------------------------------------------------------------------
    */

    Route::prefix('scan')->name('scan.')->group(function () {

        Route::view('/', 'pages.scan.index')
            ->name('index');

        Route::view('/detail-loading', 'pages.scan.detail-loading')
            ->name('detail-loading');

        Route::get('/history', [ScanHistoryController::class, 'index'])
            ->name('history.index');

        Route::get('/history/{history}', [ScanHistoryController::class, 'show'])
            ->whereNumber('history')
            ->name('history.show');

        Route::get('/history/{history}/image', [ScanHistoryController::class, 'image'])
            ->whereNumber('history')
            ->name('history.image');

        Route::post('/history/{history}/reference', [ScanHistoryController::class, 'reference'])
            ->whereNumber('history')
            ->middleware('throttle:30,1')
            ->name('history.reference');

        Route::controller(ScanController::class)->group(function () {

            Route::post('/upload', 'upload')
                ->name('upload');

            Route::get('/reference', 'reference')
                ->middleware('throttle:30,1')
                ->name('reference');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::prefix('profile')
        ->name('profile.')
        ->controller(ProfileController::class)
        ->group(function () {

            Route::get('/', 'show')
                ->name('show');

            Route::put('/', 'update')
                ->name('update');
        });

    /*
    |--------------------------------------------------------------------------
    | Fish
    |--------------------------------------------------------------------------
    */

    Route::prefix('fish')
        ->name('fish.')
        ->controller(FishController::class)
        ->where(['taxonId' => '[1-9][0-9]{0,9}'])
        ->group(function () {

            Route::get('/', 'index')
                ->name('index');

            Route::get('/search', 'search')
                ->name('search');

            Route::get('/taxa/{taxonId}', 'show')
                ->name('show');

            Route::get('/taxa/{taxonId}/extras', 'extras')
                ->name('extras');

            Route::get('/taxa/{taxonId}/photos', 'photos')
                ->name('photos');
        });

    /*
    |--------------------------------------------------------------------------
    | Articles
    |--------------------------------------------------------------------------
    */

    Route::prefix('artikel')
        ->name('artikel.')
        ->controller(ArtikelController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->name('index');

            Route::post('/', 'store')
                ->name('store');

            Route::get('/{artikel}', 'show')
                ->name('show');

            Route::put('/{artikel}', 'update')
                ->name('update');

            Route::delete('/{artikel}', 'destroy')
                ->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/admin', [AdminController::class, 'index'])
        ->middleware('admin')
        ->name('admin.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Users - FSH-25
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/users')
        ->name('admin.users.')
        ->middleware('admin')
        ->group(function () {

            // FSH-25: Daftar pengguna
            Route::get('/', [AdminUserController::class, 'index'])
                ->name('index');

            // FSH-25: Detail pengguna
            Route::get('/{user}', [AdminUserController::class, 'show'])
                ->whereNumber('user')
                ->name('show');

            // Route edit pengguna (existing)
            Route::get('/{user}/edit', [AdminUserEditController::class, 'edit'])
                ->whereNumber('user')
                ->name('edit');

            // Route update pengguna (existing)
            Route::put('/{user}', [AdminUserEditController::class, 'update'])
                ->whereNumber('user')
                ->name('update');
        });
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Login
    Route::view('/login', 'pages.auth.login')
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');

    // Registration
    Route::view('/register', 'pages.auth.register')
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.store');
});
