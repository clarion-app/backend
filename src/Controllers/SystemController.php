<?php

namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\BlockchainManager;
use ClarionApp\Backend\ConfigEditor;
use ClarionApp\MultiChain\Facades\MultiChain;
use ClarionApp\EloquentMultiChainBridge\DataStreamRegistry;

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

    public function join(Request $request)
    {
        $url = $request->input('url');
        Log::info('Joining blockchain: ' . $url);
        return response()->json(['message' => 'Not implemented'], 501);
    }
}