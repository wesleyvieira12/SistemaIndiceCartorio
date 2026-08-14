<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // Sempre HTTPS em produção / quando APP_URL for https (atrás do Traefik)
        if ($this->app->environment('production')
            || strncmp((string) config('app.url'), 'https://', 8) === 0) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}
