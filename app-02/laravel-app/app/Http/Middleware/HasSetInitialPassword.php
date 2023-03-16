<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class HasSetInitialPassword extends BaseMiddleware
{
    /**
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()->hasSetInitialPassword()) {
            throw new AuthorizationException();
        }

        return $next($request);
    }
}
