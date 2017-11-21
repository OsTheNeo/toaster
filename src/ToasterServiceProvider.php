<?php

namespace Ostheneo\Toaster;

use Illuminate\Support\ServiceProvider;

class ToasterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Views', 'Toaster');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__ . '/Routes.php';
        $this->app->make('Ostheneo\Toaster\Controllers\ToasterController');
    }
}
