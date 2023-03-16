<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback;

use App;
use App\Services\CustomerSupport\Call\ResponseInterface;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Response;

class StoreRequestValidationException extends ValidationException
{
    public function __construct(Validator $validator)
    {
        $providerResponse = App::make(ResponseInterface::class);

        $response = Response::make($providerResponse->technicalDifficulties());

        parent::__construct($validator, $response);
    }
}
