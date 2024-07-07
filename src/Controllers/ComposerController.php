<?php
namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Composer;

// This controller will install or uninstall Composer packages and restart the default queue worker after installation.
class ComposerController extends Controller
{
    public function install(Request $request)
    {
        $path = base_path();
        $package = $request->input('package');

        // Execute the composer require command and restart the queue worker after installation.
        chdir($path);
        app()->make(Composer::class)->run(['require', $package]);

        //$output = shell_exec("cd $path; composer require $package");
        $output = shell_exec("cd $path; php artisan migrate");
        $output .= shell_exec("cd $path; php artisan queue:restart");
        return response()->json(['output' => $output]);
    }

    public function uninstall(Request $request)
    {
        $path = base_path();
        $package = $request->input('package');
        chdir($path);
        app()->make(Composer::class)->run(['remove', $package]);
        $output = shell_exec("cd $path; php artisan queue:restart");
        return response()->json(['output' => $output]);
    }
}