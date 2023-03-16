<?php

namespace App\Http\Controllers\Api\V3\Account\Phone;

use App\Actions\Models\Phone\SendCallRequest;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Phone\Call\InvokeRequest;
use App\Http\Resources\Api\V3\Account\Phone\Call\BaseResource;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CallController extends Controller
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

        $action = new SendCallRequest($phone, AuthenticationCode::TYPE_VERIFICATION);
        $action->execute();

        return (new BaseResource($phone))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
