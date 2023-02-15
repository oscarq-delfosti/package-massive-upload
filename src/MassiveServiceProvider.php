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
        $this->app->make('Delfosti\Massive\Controllers\MassiveUploadLogController');

        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Config files
        $this->publishes([
            __DIR__ . '/Config/massiveupload.php' => config_path('massiveupload.php'),
        ]);
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
