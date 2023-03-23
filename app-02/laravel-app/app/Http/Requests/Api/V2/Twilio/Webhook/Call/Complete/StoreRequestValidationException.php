<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Complete;

use App;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Response;

class StoreRequestValidationException extends ValidationException
{
    public function __construct(Validator $validator)
    {
        $response = Response::json($validator->errors());

        parent::__construct($validator, $response);
    }
}
