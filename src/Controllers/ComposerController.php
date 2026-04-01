<?php
namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Composer;
use ClarionApp\Backend\AppManager;
use ClarionApp\Backend\Rules\ComposerPackageName;
use ClarionApp\Backend\Traits\JsonErrorResponse;

// This controller will install or uninstall Composer packages and restart the default queue worker after installation.
class ComposerController extends Controller
{
    use JsonErrorResponse;
    protected $appManger;

    public function __construct()
    {
        $this->appManger = new AppManager();
    }

    public function install(Request $request)
    {
        $request->validate([
            'package' => ['required', 'string', new ComposerPackageName],
        ]);

        Log::info('Installing package: '.$request->input('package'));
        $output = $this->appManger->composerInstall($request->input('package'));
        return response()->json(['output' => $output]);
    }

    public function uninstall(Request $request)
    {
        $request->validate([
            'package' => ['required', 'string', new ComposerPackageName],
        ]);

        Log::info('Uninstalling package: '.$request->input('package'));
        $output = $this->appManger->composerUninstall($request->input('package'));
        return response()->json(['output' => $output]);
    }
}