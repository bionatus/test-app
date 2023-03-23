<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Action;

use App;
use App\Jobs\LogCommunicationRequest;
use App\Models\Call;
use App\Services\CustomerSupport\Call\ResponseInterface;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Response;

class StoreRequestValidationException extends ValidationException
{
    private string $description = 'Error validating call/action.';

    public function __construct(Validator $validator, array $payload, ?string $source, ?Call $call)
    {
        $providerResponse = App::make(ResponseInterface::class);
        $response         = Response::make($providerResponse->retryAgainLater());

        if ($call) {
            $call->complete();
            LogCommunicationRequest::dispatch($call->communication, $this->description, $payload, $response, $source,
                $validator->errors()->toArray());
        }

        parent::__construct($validator, $response);
    }
}
