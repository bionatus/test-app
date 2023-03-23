<?php

namespace App\Http\Controllers\Api\V3\Auth\Email;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Auth\Email\Login\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Email\Login\BaseResource;
use App\Models\Device;
use App\Models\User;
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
    public function __invoke(InvokeRequest $request, JWTAuth $auth)
    {
        $credentials = [
            'email'    => $request->get(RequestKeys::EMAIL),
            'password' => $request->get(RequestKeys::PASSWORD),
        ];

        try {
            $token = $auth->attempt($credentials);
            if (!$token) {
                throw ValidationException::withMessages([
                    RequestKeys::PASSWORD => [Lang::get('auth.failed')],
                ]);
            }

            /** @var User $user */
            $user = $auth->user();

            if ($user->disabled_at) {
                throw ValidationException::withMessages([
                    RequestKeys::EMAIL => [Lang::get('auth.account_disabled')],
                ]);
            }

            Device::with(['pushNotificationToken'])->updateOrCreate([
                'udid' => $request->get(RequestKeys::DEVICE),
            ], [
                'udid'        => $request->get(RequestKeys::DEVICE),
                'app_version' => $request->get(RequestKeys::VERSION),
                'user_id'     => $user->getKey(),
                'token'       => $token,
            ]);

            return (new BaseResource($user, $token))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
        } catch (JWTException $exception) {
            return Response::json(['data' => 'Could not create the token.'], HttpResponse::HTTP_UNAUTHORIZED);
        }
    }
}
