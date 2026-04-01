<?php
namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\AppManager;
use ClarionApp\Backend\Models\AppPackage;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use ClarionApp\Backend\Rules\ComposerPackageName;
use ClarionApp\Backend\Traits\JsonErrorResponse;

// This controller will list available apps from the Clarion Store
class AppController extends Controller
{
    use JsonErrorResponse;
    protected $appManager;
    protected $httpClient;

    public function __construct(GuzzleClient $httpClient)
    {
        $this->appManager = new AppManager();
        $this->httpClient = $httpClient;
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
        $request->validate([
            'package' => ['required', 'string', new ComposerPackageName],
        ]);

        $output = $this->appManager->appInstall($request->input('package'));
        return response()->json($output);
    }

    public function uninstall(Request $request)
    {
        $request->validate([
            'package' => ['required', 'string', new ComposerPackageName],
        ]);

        $output = $this->appManager->appUninstall($request->input('package'));
        return response()->json($output);
    }

    private function getOrganizations()
    {
        $orgs = [];
        try {
            $response = $this->httpClient->get(config('clarion.store_url').'/api/organizations');
            $results = json_decode($response->getBody()->getContents());
            foreach($results as $result) $orgs[] = $result->name;
        } catch (RequestException $e) {
            Log::error('Failed to fetch organizations: ' . $e->getMessage());
        }
        return $orgs;
    }

    private function getPackages($org)
    {
        $packages = [];
        try {
            $response = $this->httpClient->get(config('clarion.store_url')."/api/organizations/$org");
            $results = json_decode($response->getBody()->getContents());
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
        } catch (RequestException $e) {
            Log::error("Failed to fetch packages for $org: " . $e->getMessage());
        }
        return $packages;
    }
}
