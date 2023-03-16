<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthenticateBasecampUser
{
    public function handle(Request $request, Closure $next)
    {
        $key = Config::get('basecamp.token.key');

        if (!Hash::check($key, $request->bearerToken())) {
            throw new UnauthorizedHttpException('jwt-auth', 'Invalid token');
        }

        return $next($request);
    }
}
