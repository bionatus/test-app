<?php

namespace App\Http\Controllers\Api\V3\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Auth\Refresh\BaseResource;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

class RefreshController extends Controller
{
    public function __invoke(Request $request, JWTAuth $jwtAuth)
    {
        $user  = Auth::user();
        $token = $jwtAuth->refresh();

        return (new BaseResource($user, $token))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
