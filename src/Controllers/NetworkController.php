<?php

namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Models\NodeRegistry;
use ClarionApp\Backend\Models\LocalNode;
use ClarionApp\Backend\Models\BlockchainRequest;
use ClarionApp\Backend\BlockchainManager;

class NetworkController extends Controller
{
    public function index()
    {
        $app_url = explode(":", config('app.url'));
        $ip = str_replace("//", "", $app_url[1]);
        return [
            'name' => 'clarion',
            'url' => 'clarion@'.$ip.':'.config('multichain.node_port')
        ];
    }

    public function join(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');

        $node = BlockchainRequest::where('node_id', $id)->first();
        if(!$node)
        {
            $node = BlockchainRequest::create([
                'node_id' => $id,
            ]);
        }
        return response()->json($node);
    }

    /* Called by existing node */
    public function completeJoin(Request $request)
    {
        $name = $request->input(name);
        
        $manager = new BlockchainManager();
        $manager->config($name);
        return response()->json([], 200);
    }

    public function accept(Request $request)
    {
        $id = $request->input('id');
        $node = LocalNode::where('node_id', $id)->first();
        if(!$node)
        {
            return response()->json(['error' => 'Node not found'], 404);
        }

        MultiChain::grant($node->wallet_address, 'connect,admin,activate,create');
        $responseUrl = $node->backend_url.'/api/clarion/network/complete_join';

        $body = new stdClass;
        $body->name = 'clarion';

        $client = new \GuzzleHttp\Client();
        $response = $client->post($responseUrl, [
            'json' => $body,
        ]);
        return response()->json([], 200);
    }

    public function requestsIndex()
    {
        $requests = BlockchainRequest::all();
        return $requests;
    }
}