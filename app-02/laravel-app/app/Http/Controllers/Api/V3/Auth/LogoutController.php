<?php

namespace App\Http\Controllers\Api\V3\Auth;

use App\Http\Controllers\Controller;
use App\Models\Device\Scopes\ByToken;
use Auth;
use Illuminate\Http\Request;
use Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class LogoutController extends Controller
{
    /** @noinspection PhpRedundantCatchClauseInspection */
    public function __invoke(Request $request, JWTAuth $auth)
    {
        try {
            $user  = Auth::user();
            $token = $request->bearerToken();

            $auth->setToken($token);
            $auth->invalidate(true);

            Auth::logout();
            $user->devices()->scoped(new ByToken($token))->delete();

            return Response::noContent();
        } catch (JWTException $exception) {
            // silently ignores the exception
            return Response::noContent();
        }
    }
}
