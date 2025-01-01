<?php

namespace ClarionApp\Backend;

use Illuminate\Support\ServiceProvider;
use ClarionApp\Backend\Commands\SetupClarion;
use ClarionApp\Backend\Commands\SetupMariaDB;
use ClarionApp\Backend\Commands\SetupNodeID;
use ClarionApp\Backend\Commands\BlockNotify;
use ClarionApp\Backend\Commands\ClarionScan;
use ClarionApp\Backend\Commands\TestAPI;
use ClarionApp\Backend\Models\User;
use ClarionApp\Backend\Controllers\UserController;
use ClarionApp\Backend\Jobs\NodeDiscovery;
use Illuminate\Console\Scheduling\Schedule;

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
            SetupNodeID::class,
            BlockNotify::class,
            ClarionScan::class,
            TestAPI::class
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

        $cors = config('cors.paths');
        $cors[] = 'docs/api.json';
        config(['cors.paths'=>$cors]);

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

            app('router')->get('/api/user', [UserController::class, "index"])->middleware('auth:api');

            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function() {
                $result = shell_exec('pgrep -c -f "php artisan queue:work --queue=default"');
                if($result == "2\n")
                {
                    dispatch(new NodeDiscovery());
                }
            })->everyFiveSeconds();
        });
    }
}
