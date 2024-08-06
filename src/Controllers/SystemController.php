<?php

namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\BlockchainManager;
use ClarionApp\Backend\ConfigEditor;

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