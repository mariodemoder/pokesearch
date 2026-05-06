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
        RateLimiter::for('pokemon-api', function (Request $request) {
            $maxAttempts = (int) env('POKEMON_API_RATE_LIMIT_PER_MINUTE', 30);

            return Limit::perMinute(max(1, $maxAttempts))->by($request->ip());
        });
    }
}
