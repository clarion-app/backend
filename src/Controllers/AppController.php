<?php
namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\AppManager;
use ClarionApp\Backend\Models\AppPackage;

// This controller will list available apps from the Clarion Store
class AppController extends Controller
{
    protected $appManager;

    public function __construct()
    {
        $this->appManager = new AppManager();
    }

    public function index()
    {
        $apps = json_decode(file_get_contents('https://store.clarion.app'));
        $packageList = [];
        foreach($apps as &$app)
        {
            $packageList[] = $app->package;
        }

        $installedApps = AppPackage::whereIn('name', $packageList)->get();
        foreach($apps as &$app)
        {
            $app->installed = false;
            foreach($installedApps as $installedApp)
            {
                if($installedApp->name == $app->package)
                {
                    $app->installed = true;
                }
            }
        }
        
        return response()->json($apps);
    }

    public function install(Request $request)
    {
        $output = $this->appManager->appInstall($request->input('package'));
        return response()->json(['output' => $output]);
    }

    public function uninstall(Request $request)
    {
        $output = $this->appManager->appUninstall($request->input('package'));
        return response()->json(['output' => $output]);
    }
}