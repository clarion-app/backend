<?php
namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\AppManager;

// This controller will list available apps from the Clarion Store
class AppStoreController extends Controller
{
    protected $appManager;

    public function __construct()
    {
        $this->appManager = new AppManager();
    }

    public function index()
    {
        $packages = json_decode(file_get_contents('https://store.clarion.app'));
        return response()->json($packages);
    }

    public function install(Request $request)
    {
        $output = $this->appManager->composerInstall($request->input('package'));
        return response()->json(['output' => $output]);
    }

    public function uninstall(Request $request)
    {
        $output = $this->appManager->composerUninstall($request->input('package'));
        return response()->json(['output' => $output]);
    }
}