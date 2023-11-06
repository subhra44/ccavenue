<?php

namespace Subhra\CCAvenue\Providers;

use Illuminate\Support\ServiceProvider;
use Subhra\CCAvenue\CCAvenue;

class CcavenueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ccavenue.php' => config_path('ccavenue.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ccavenue.php',
            'ccavenue'
        );

        $this->app->singleton('ccavenue', function () {
            return new CCAvenue();
        });
    }
}
