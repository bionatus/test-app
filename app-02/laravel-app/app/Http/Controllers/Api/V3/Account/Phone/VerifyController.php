<?php

namespace App\Http\Controllers\Api\V3\Account\Phone;

use App\Events\Phone\Verified;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Phone\Verify\InvokeRequest;
use App\Http\Resources\Api\V3\Account\Phone\Verify\BaseResource;
use App\Models\AuthenticationCode\Scopes\VerificationType;
use Auth;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class VerifyController extends Controller
{
    public function __invoke(InvokeRequest $request, JWTAuth $auth)
    {
        $user  = Auth::User();
        $phone = $request->phone();

        $user->phone()->delete();

        $phone->verify();
        $phone->user()->associate($user);
        $phone->save();

        $phone->authenticationCodes()->scoped(new VerificationType())->delete();

        Verified::dispatch($phone);

        return (new BaseResource($phone))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
