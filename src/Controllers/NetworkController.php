<?php

namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Models\NodeRegistry;

class NetworkController extends Controller
{
    public function join(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');

        $node = NodeRegistry::find($id);
        if(!$node)
        {
            $node = NodeRegistry::create(['id' => $id, 'name' => $name]);
        }
        return response()->json($node);
    }
}