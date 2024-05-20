<?php

namespace ClarionApp\Backend;

use Illuminate\Support\ServiceProvider;
use ClarionApp\Backend\Commands\SetupClarion;
use ClarionApp\Backend\Commands\SetupMariaDB;
use ClarionApp\Backend\Commands\SetupMultichain;
use ClarionApp\Backend\Commands\SetupNodeID;
use ClarionApp\Backend\Commands\BlockNotify;
use ClarionApp\Backend\Commands\RebuildFrontendRoutes;

class ClarionBackendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            SetupClarion::class,
            SetupMariaDB::class,
            SetupMultichain::class,
            SetupNodeID::class,
            BlockNotify::class,
            RebuildFrontendRoutes::class
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if(!$this->app->routesAreCached())
        {
            require __DIR__.'/Routes.php';
        }

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('clarion.php'),
        ], 'config');


        $this->app->booted(function () {
            app('router')->get('/', function() {
?>
<h1>Hello</h1>
<?php
            })->middleware('web');
        });
    }
}
