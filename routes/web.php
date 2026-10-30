
<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
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

        Route::view('/history', 'pages.scan.history.index')
            ->name('history.index');

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
