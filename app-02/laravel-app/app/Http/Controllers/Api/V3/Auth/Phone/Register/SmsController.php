<?php

namespace App\Http\Controllers\Api\V3\Auth\Phone\Register;

use App\Actions\Models\Phone\SendSMSRequest;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Sms\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Phone\Register\Sms\BaseResource;
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
    public function __invoke(InvokeRequest $request)
    {
        if (!($phone = $request->phone())) {
            $phone = Phone::create([
                'number'       => $request->get(RequestKeys::PHONE),
                'country_code' => $request->get(RequestKeys::COUNTRY_CODE),
            ]);
        }

        $authenticationCodeType = AuthenticationCode::TYPE_VERIFICATION;
        $message = Lang::get('validation.in', ['attribute' => RequestKeys::PHONE]);
        $action  = new SendSMSRequest($phone, $authenticationCodeType, $message);
        $action->execute();

        return (new BaseResource($phone))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
