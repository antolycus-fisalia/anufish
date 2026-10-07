<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{

    RateLimiter::for('register', function (Request $request) {
        return Limit::perMinute(5)
            ->by($request->ip());
    });

    RateLimiter::for('login', function (Request $request) {
        $email = Str::lower((string) $request->input('email'));

    Gate::policy(User::class, UserPolicy::class);

        return Limit::perMinute(5)
            ->by($email . '|' . $request->ip());
    });
}
}
