<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class ProvideLiveUser
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('live');

        return $next($request);
    }
}
