<?php

namespace ClarionApp\Backend;

use Illuminate\Support\ServiceProvider;
use ClarionApp\Backend\Commands\SetupClarion;
use ClarionApp\Backend\Commands\SetupMariaDB;
use ClarionApp\Backend\Commands\SetupMultichain;
use ClarionApp\Backend\Commands\SetupNodeID;
use ClarionApp\Backend\Commands\BlockNotify;
use ClarionApp\Backend\Models\User;

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
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        
        config(['auth.providers.users.model' => User::class]);
        $guards = config('auth.guards');
            $guards['api'] = [
                'driver' => 'passport',
                'provider' => 'users',
                'hash' => false
            ];
        config(['auth.guards'=>$guards]);

        if(!$this->app->routesAreCached())
        {
            require __DIR__.'/Routes.php';
        }

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('clarion.php'),
        ], 'clarion-config');


        $this->app->booted(function () {
            app('router')->get('/', function() {
?>
<h1>Hello</h1>
<?php
            })->middleware('web');
        });
    }
}
