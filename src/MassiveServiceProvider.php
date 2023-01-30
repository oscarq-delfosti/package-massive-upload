<?php

namespace Delfosti\Massive;

use Illuminate\Support\ServiceProvider;

class MassiveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Controllers
        $this->app->make('Delfosti\Massive\Controllers\MassiveController');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
