<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AcceptsJSON
{
    public function handle($request, Closure $next)
    {
        /** @var Request $request */
        $request->headers->set('accept', 'application/json');

        return $next($request);
    }
}
