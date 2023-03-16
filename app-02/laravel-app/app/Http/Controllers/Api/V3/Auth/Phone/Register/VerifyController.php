<?php

namespace App\Http\Controllers\Api\V3\Auth\Phone\Register;

use App\Events\Phone\Verified;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Verify\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Phone\Register\Verify\BaseResource;
use App\Models\AuthenticationCode\Scopes\VerificationType;
use App\Models\Phone;
use Config;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

class VerifyController extends Controller
{
    public function __invoke(InvokeRequest $request, JWTAuth $auth, Phone $unverifiedPhone)
    {
        $phone = $unverifiedPhone;
        $phone->verify();
        $phone->save();

        $ttl = Config::get('communications.phone.verification.ttl');
        $auth->factory()->setTTL($ttl);
        $payload = $auth->makePayload($phone);
        $token   = $auth->manager()->encode($payload);

        $phone->authenticationCodes()->scoped(new VerificationType())->delete();

        Verified::dispatch($phone);

        return (new BaseResource($phone, $token))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
