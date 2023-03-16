<?php

namespace App\Http\Controllers\LiveApi\V1\Auth\Email;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Auth\Email\Login\BaseResource;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lang;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller
{
    /**
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request, JWTAuth $auth)
    {
        $credentials = [
            'email'    => $request->get(RequestKeys::EMAIL),
            'password' => $request->get(RequestKeys::PASSWORD),
            'type'     => Staff::TYPE_OWNER,
        ];

        try {
            $token = $auth->attempt($credentials);
            if (!$token) {
                throw ValidationException::withMessages([
                    RequestKeys::PASSWORD => [Lang::get('auth.failed')],
                ]);
            }

            /** @var Staff $staff */
            $staff = $auth->user();

            return (new BaseResource($staff, $token))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
        } catch (JWTException $exception) {
            return Response::json(['data' => 'Could not create the token.'], HttpResponse::HTTP_UNAUTHORIZED);
        }
    }
}
