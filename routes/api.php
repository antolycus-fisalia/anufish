<?php

use App\Http\Controllers\Admin\AdminArtikelListController;
use App\Http\Controllers\Admin\AdminArtikelModerationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminUserEditController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\ArtikelSayaNotificationController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\PublicArticleDetailController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ScanHistoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/up', [HealthController::class, 'live'])->name('health.live');
    Route::get('/ready', [HealthController::class, 'ready'])->name('health.ready');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register')->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

    Route::middleware(['auth:sanctum', 'active', 'throttle:api'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/home', HomeController::class)->name('home');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::prefix('fish')->name('fish.')->controller(FishController::class)
            ->where(['taxonId' => '[1-9][0-9]{0,9}'])->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/search', 'search')->name('search');
                Route::get('/taxa/{taxonId}', 'show')->name('show');
                Route::get('/taxa/{taxonId}/extras', 'extras')->name('extras');
                Route::get('/taxa/{taxonId}/photos', 'photos')->name('photos');
            });

        Route::prefix('scan')->name('scan.')->group(function () {
            Route::post('/upload', [ScanController::class, 'upload'])->name('upload');
            Route::get('/reference', [ScanController::class, 'reference'])->middleware('throttle:30,1')->name('reference');
            Route::get('/history', [ScanHistoryController::class, 'index'])->name('history.index');
            Route::get('/history/{history}', [ScanHistoryController::class, 'show'])->whereNumber('history')->name('history.show');
            Route::get('/history/{history}/image', [ScanHistoryController::class, 'image'])->whereNumber('history')->name('history.image');
            Route::post('/history/{history}/reference', [ScanHistoryController::class, 'reference'])
                ->whereNumber('history')->middleware('throttle:30,1')->name('history.reference');
        });

        Route::get('/articles', [PublicArticleController::class, 'index'])->name('public-articles.index');
        Route::get('/articles/{artikel}', [PublicArticleDetailController::class, 'show'])
            ->whereNumber('artikel')->name('public-articles.show');

        Route::prefix('my/articles')->name('articles.')->controller(ArtikelController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{artikel}', 'show')->whereNumber('artikel')->name('show');
            Route::put('/{artikel}', 'update')->whereNumber('artikel')->name('update');
            Route::delete('/{artikel}', 'destroy')->whereNumber('artikel')->name('destroy');
        });

        Route::prefix('notifications')->name('notifications.')->controller(ArtikelSayaNotificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/read-all', 'markAllRead')->name('read-all');
            Route::post('/{notificationId}/read', 'markRead')->whereUuid('notificationId')->name('read');
        });

        Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [AdminUserController::class, 'show'])->whereNumber('user')->name('users.show');
            Route::put('/users/{user}', [AdminUserEditController::class, 'update'])->whereNumber('user')->name('users.update');
            Route::get('/articles', [AdminArtikelListController::class, 'index'])->name('articles.index');
            Route::get('/articles/{artikel}', [AdminArtikelListController::class, 'show'])->whereNumber('artikel')->name('articles.show');
            Route::post('/articles/{artikel}/approve', [AdminArtikelModerationController::class, 'approve'])->whereNumber('artikel')->name('articles.approve');
            Route::post('/articles/{artikel}/reject', [AdminArtikelModerationController::class, 'reject'])->whereNumber('artikel')->name('articles.reject');
            Route::post('/articles/{artikel}/delete', [AdminArtikelModerationController::class, 'delete'])->whereNumber('artikel')->name('articles.delete');
        });
    });
});
