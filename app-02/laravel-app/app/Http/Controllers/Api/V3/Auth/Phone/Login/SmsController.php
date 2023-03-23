<?php

namespace App\Http\Controllers\Api\V3\Auth\Phone\Login;

use App\Actions\Models\Phone\SendSMSRequest;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Auth\Phone\Login\Sms\BaseResource;
use App\Models\AppNotification;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Illuminate\Validation\ValidationException;
use Lang;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SmsController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Phone $assignedVerifiedPhone)
    {
        $user = $assignedVerifiedPhone->user;

        if ($user->disabled_at) {
            throw ValidationException::withMessages([
                RequestKeys::PHONE => [Lang::get('auth.account_disabled')],
            ]);
        }

        $authenticationCodeType = AuthenticationCode::TYPE_LOGIN;
        $message                = "SMS's are not possible for the provided phone.";
        $action                 = new SendSMSRequest($assignedVerifiedPhone, $authenticationCodeType, $message);
        $action->execute();

        return (new BaseResource($assignedVerifiedPhone))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
