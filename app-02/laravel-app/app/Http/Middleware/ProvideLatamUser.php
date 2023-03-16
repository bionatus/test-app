<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class ProvideLatamUser
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('latam');

        return $next($request);
    }
}
