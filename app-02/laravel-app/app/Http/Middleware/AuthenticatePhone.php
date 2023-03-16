<?php

namespace App\Http\Middleware;

use App\Models\Phone;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthenticatePhone extends BaseMiddleware
{
    /**
     * @throws JWTException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->authenticate($request);

        $token = $this->auth->parseToken();

        if (sha1(Phone::class) != $token->getClaim('prv')) {
            throw new UnauthorizedHttpException('jwt-auth', 'User not found');
        }

        return $next($request);
    }
}
