<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthenticateUser extends BaseMiddleware
{
    /**
     * @throws JWTException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->authenticate($request);

        $token = $this->auth->parseToken();

        if (!in_array($token->getClaim('prv'), [sha1(\App\User::class), sha1(\App\Models\User::class)])) {
            throw new UnauthorizedHttpException('jwt-auth', 'User not found');
        }

        if (!!$this->auth->user()->disabled_at) {
            throw new AccessDeniedHttpException('User is disabled');
        }

        return $next($request);
    }
}
