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
        $this->mergeConfigFrom(__DIR__.'/../config/toaster.php', 'toaster');
        $this->publishes([__DIR__.'/../config/toaster.php' => config_path('toaster.php'),],"config");

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Toaster');
        $this->publishes([__DIR__.'/../resources/views' => resource_path('views/vendor/Toaster'),],'views');

        $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang'),],'dictionary');
    }
}