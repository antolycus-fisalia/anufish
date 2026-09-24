<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): array {
            $email = $request->string('email')->trim()->lower()->toString();

            return [
                Limit::perMinute(5)->by('login:email-ip:'.$email.'|'.$request->ip()),
                Limit::perMinute(20)->by('login:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('registration', function (Request $request): Limit {
            return Limit::perMinute(3)->by('registration:ip:'.$request->ip());
        });
    }
}
