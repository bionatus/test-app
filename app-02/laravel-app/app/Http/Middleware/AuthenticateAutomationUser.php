<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthenticateAutomationUser
{
    public function handle(Request $request, Closure $next)
    {
        $key = Config::get('automation.token.key');

        if (!Hash::check($key, $request->bearerToken()) || App::isProduction()) {
            throw new UnauthorizedHttpException('jwt-auth', 'User not found');
        }

        return $next($request);
    }
}
