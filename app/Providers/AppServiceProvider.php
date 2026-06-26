<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Behind Render's TLS-terminating proxy the app receives plain HTTP,
        // so force every generated URL (forms, links, redirects) to use HTTPS
        // in production. This prevents "form is not secure" browser warnings.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
