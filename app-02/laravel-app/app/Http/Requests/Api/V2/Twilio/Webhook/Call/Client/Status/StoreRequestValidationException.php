<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status;

use App;
use App\Jobs\LogCommunicationRequest;
use App\Models\Call;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Response;

class StoreRequestValidationException extends ValidationException
{
    private string $description = 'Error validating client/status.';

    public function __construct(Validator $validator, array $payload, ?string $source, ?Call $call)
    {
        $response = Response::json($validator->errors());

        if ($call) {
            $call->complete();
            LogCommunicationRequest::dispatch($call->communication, $this->description, $payload,
                $response, $source, $validator->errors()->toArray());
        }

        parent::__construct($validator, $response);
    }
}
