<?php

namespace OsTheNeo\Toaster;

use Illuminate\Support\ServiceProvider;

class ToasterServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Toaster');
        $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang'),],'dictionary');
    }
}