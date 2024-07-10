<?php
namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Composer;
use ClarionApp\Backend\AppManager;

// This controller will install or uninstall Composer packages and restart the default queue worker after installation.
class ComposerController extends Controller
{
    protected $appManger;

    public function __construct()
    {
        $this->appManger = new AppManager();
    }

    public function install(Request $request)
    {
        Log::info('Installing package: '.$request->input('package'));
        $output = $this->appManger->composerInstall($request->input('package'));
        return response()->json(['output' => $output]);
    }

    public function uninstall(Request $request)
    {
        Log::info('Uninstalling package: '.$request->input('package'));
        $output = $this->appManger->composerUninstall($request->input('package'));
        return response()->json(['output' => $output]);
    }
}