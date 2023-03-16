<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class ProvideLatamPhone
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('phone');

        return $next($request);
    }
}
