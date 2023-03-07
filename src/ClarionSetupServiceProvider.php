<?php

namespace ClarionApp\ClarionSetup;

use Illuminate\Support\ServiceProvider;
use ClarionApp\ClarionSetup\Commands\SetupClarion;
use ClarionApp\ClarionSetup\Commands\SetupMariaDB;
use ClarionApp\ClarionSetup\Commands\SetupMultichain;
use ClarionApp\ClarionSetup\Commands\SetupNodeID;

class ClarionSetupServiceProvider extends ServiceProvider
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
            SetupNodeID::class
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

        $this->app->booted(function () {
            app('router')->get('/', function() {
?>
<h1>Hello</h1>
<?php
            })->middleware('web');
        });
    }
}
