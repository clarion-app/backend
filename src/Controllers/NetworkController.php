<?php

namespace ClarionApp\Backend\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class NetworkController extends Controller
{
    public function index(Request $request)
    {
        $action = $request->input('action');
        $arguments = $request->input('arguments');
        Log::info('NetworkController@index', ['action' => $action, 'arguments' => $arguments]);
    }
}