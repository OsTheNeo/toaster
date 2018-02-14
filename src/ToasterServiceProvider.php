<?php

namespace Ostheneo\Toaster;

use Illuminate\Support\ServiceProvider;

class ToasterServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'test');
    }
}
