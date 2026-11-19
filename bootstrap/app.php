<?php

use App\Http\Controllers\HealthController;
use App\Http\Middleware\ApiContext;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function (): void {
            Route::get('/up', [HealthController::class, 'live'])->name('health.live');
            Route::get('/ready', [HealthController::class, 'ready'])->name('health.ready');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestsDuringMaintenance(except: ['up', 'ready', 'api/v1/up', 'api/v1/ready']);
        $middleware->web(append: [SetLocale::class]);
        $middleware->api(prepend: [ApiContext::class]);
        $middleware->alias(['admin' => EnsureAdmin::class, 'active' => EnsureActiveUser::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
