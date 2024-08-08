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

class SystemController extends Controller
{
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
        $id = $request->input('node_id');
        $node = LocalNode::where('node_id', $id)->first();
        if(!$node)
        {
            return response()->json(['error' => 'Node '.$id.' not found'], 404);
        }

        $url = $node->backend_url.'/api/clarion/network';
        $result = json_decode(file_get_contents($url));
        Log::info('Joining blockchain: ' . $url);
        
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
        return response()->json(['message' => 'Not implemented'], 201);
    }
}