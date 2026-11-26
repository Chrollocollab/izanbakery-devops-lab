<?php

namespace App\Providers;

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
        // ROLLBACK DRILL: crash only in production, so CI tests (APP_ENV=testing) still pass.
        // Every request and artisan command then fails on the server -> /up fails -> rollback.
        // Revert this commit right after the drill.
        if ($this->app->environment('production')) {
            throw new \RuntimeException('Rollback drill: deliberately broken deploy');
        }
    }
}
