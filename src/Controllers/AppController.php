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
        $packages = [];
        $orgs = $this->getOrganizations();
        foreach($orgs as $org)
        {
            $packages = array_merge($packages, $this->getPackages($org));
        }

        $installedApps = AppPackage::where('installed', true)->get();
        foreach($packages as &$package)
        {
            $package['installed'] = false;
            foreach($installedApps as $installedApp)
            {
                if($installedApp->organization != $package['organization']) continue;
                if($installedApp->name != $package['name']) continue;
                $package['installed'] = true;
            }
        }
        
        return response()->json($packages);
    }

    public function install(Request $request)
    {
        $output = $this->appManager->appInstall($request->input('package'));
        return response()->json($output);
    }

    public function uninstall(Request $request)
    {
        $output = $this->appManager->appUninstall($request->input('package'));
        return response()->json($output);
    }

    private function getOrganizations()
    {
        $orgs = [];
        $results = json_decode(file_get_contents(config('clarion.store_url').'/api/organizations'));
        foreach($results as $result) $orgs[] = $result->name;
        return $orgs;
    }

    private function getPackages($org)
    {
        $packages = [];
        $results = json_decode(file_get_contents(config('clarion.store_url')."/api/organizations/$org"));
        foreach($results->packages as $package)
        {
            $package = [
                'name' => $package->name,
                'title' => $package->title,
                'description' => $package->description,
                'organization' => $org
            ];
            $packages[] = $package;
        }
        return $packages;
    }
}
