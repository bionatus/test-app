<?php

namespace App\Http\Controllers\Api\V3\Auth\Phone\Login;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Auth\Phone\Login\Verify\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Phone\Login\Verify\BaseResource;
use App\Models\AuthenticationCode\Scopes\ByCode;
use App\Models\AuthenticationCode\Scopes\LoginType;
use App\Models\Device;
use App\Models\Phone;
use App\Models\Scopes\ByCreatedAfter;
use Config;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Lang;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tymon\JWTAuth\JWTAuth;

class VerifyController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(InvokeRequest $request, JWTAuth $auth, Phone $assignedVerifiedPhone)
    {
        $expiration    = Carbon::now()->subSeconds(Config::get('communications.sms.code.reset_after'));
        $authenticated = !!$assignedVerifiedPhone->authenticationCodes()
            ->scoped(new ByCreatedAfter($expiration))
            ->scoped(new ByCode($request->get(RequestKeys::CODE)))
            ->count();

        if (!$authenticated) {
            throw ValidationException::withMessages([
                RequestKeys::CODE => [Lang::get('auth.failed')],
            ]);
        }

        $assignedVerifiedPhone->authenticationCodes()->scoped(new LoginType())->delete();
        $user  = $assignedVerifiedPhone->user;
        $token = $auth->fromUser($user);

        Device::with(['pushNotificationToken'])->updateOrCreate([
            'udid' => $request->get(RequestKeys::DEVICE),
        ], [
            'udid'        => $request->get(RequestKeys::DEVICE),
            'app_version' => $request->get(RequestKeys::VERSION),
            'user_id'     => $user->getKey(),
            'token'       => $token,
        ]);

        return (new BaseResource($user, $token))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
