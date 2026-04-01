<?php

namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\BlockchainManager;
use ClarionApp\Backend\ConfigEditor;
use ClarionApp\MultiChain\Facades\MultiChain;
use ClarionApp\EloquentMultiChainBridge\DataStreamRegistry;
use ClarionApp\Backend\Models\LocalNode;
use GuzzleHttp\Client as GuzzleClient;
use ClarionApp\Backend\Traits\JsonErrorResponse;
use stdClass;

class SystemController extends Controller
{
    use JsonErrorResponse;
    public function create()
    {
        $manager = new BlockchainManager();
        if($manager->exists('clarion'))
        {
            return response()->json(['error' => 'Blockchain already exists'], 400);
        }
        $manager->create('clarion');

        $stream_name = \Str::uuid();
        MultiChain::create('stream', $stream_name, false);
        DataStreamRegistry::create([
            'data_stream'=>$stream_name,
            'class_name'=>'ClarionApp\\Backend\\Models\\NpmPackage'
        ]);
        DataStreamRegistry::create([
            'data_stream'=>$stream_name,
            'class_name'=>'ClarionApp\\Backend\\Models\\ComposerPackage'
        ]);
        DataStreamRegistry::create([
            'data_stream'=>$stream_name,
            'class_name'=>'ClarionApp\\Backend\\Models\\AppPackage'
        ]);
        ConfigEditor::update('eloquent-multichain-bridge.disabled', false);
        return response()->json(['message' => 'Blockchain created'], 200);
    }

    /* Called by frontend */
    public function join(Request $request)
    {
        $request->validate([
            'node_id' => ['required', 'string'],
        ]);

        $id = $request->input('node_id');
        $node = LocalNode::where('node_id', $id)->first();
        if(!$node)
        {
            return response()->json(['error' => 'Node '.$id.' not found'], 404);
        }

        $url = $node->backend_url.'/api/clarion/network';
        $httpClient = app(GuzzleClient::class);
        $response = $httpClient->get($url);
        $result = json_decode($response->getBody()->getContents());
        
        $manager = new BlockchainManager();
        $wallet_address = $manager->requestJoin($result->url);

        $hostname = gethostname();

        $body = new stdClass;
        $body->id = config('clarion.node_id');
        $body->name = $hostname;
        $body->wallet_address = $wallet_address;

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url."/join", [
            'json' => $body,
        ]);
        Log::info(print_r($response, true));
        return $this->notImplementedResponse();
    }
}