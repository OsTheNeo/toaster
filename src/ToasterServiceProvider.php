<?php

namespace Ostheneo\Toaster;

use Illuminate\Support\ServiceProvider;

class ToasterServiceProvider extends ServiceProvider {
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Toaster');
        $this->publishes([
            __DIR__ . '/path/to/assets' => public_path('packages/ostheneo/toaster/Vendor'),
        ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        include __DIR__ . '/Routes.php';
        $this->app->make('Ostheneo\Toaster\Controllers\ToasterController');
    }
}
