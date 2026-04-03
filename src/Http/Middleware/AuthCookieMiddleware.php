<?php

namespace ClarionApp\Backend\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthCookieMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('clarion_token');

        if ($token && !$request->headers->has('Authorization')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
