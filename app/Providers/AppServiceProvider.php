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
        // ROLLBACK DRILL: crash only on WEB requests in production.
        // Console commands (composer's package:discover, migrate, queue:work) still boot fine,
        // and CI tests run as APP_ENV=testing, so CI stays green. But /up returns 500 on the server
        // -> web healthcheck fails -> deploy.sh rolls back. Revert right after the drill.
        if ($this->app->environment('production') && ! $this->app->runningInConsole()) {
            throw new \RuntimeException('Rollback drill: deliberately broken deploy');
        }
    }
}
