<?php

namespace App\Http\Middleware;

use App\Models\Staff;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthenticateStaff extends BaseMiddleware
{
    /**
     * @throws JWTException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->authenticate($request);

        $token = $this->auth->parseToken();

        if (sha1(Staff::class) != $token->getClaim('prv')) {
            throw new UnauthorizedHttpException('jwt-auth', 'User not found');
        }

        return $next($request);
    }
}
