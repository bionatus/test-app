<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Action;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Call;
use App\Models\Communication;
use App\Rules\Call\Exists;
use App\Rules\Call\NotExpired;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    private Exists $callExists;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $this->callExists = App::make(Exists::class, ['provider' => Communication::PROVIDER_TWILIO]);

        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    protected function failedValidation(Validator $validator)
    {
        $source = $this->route() ? $this->route()->getName() : null;
        $call   = $this->callExists->call();

        throw new StoreRequestValidationException($validator, $this->all(), $source, $call->exists ? $call : null);
    }

    public function rules(): array
    {
        return [
            RequestKeys::TWILIO_CALL_SID => [
                'required',
                'string',
                'bail',
                $this->callExists,
                new NotExpired($this->callExists),
            ],

            RequestKeys::TWILIO_DIAL_CALL_STATUS => [
                'required',
                Rule::in([
                    Call::TWILIO_DIAL_CALL_STATUS_ANSWERED,
                    Call::TWILIO_DIAL_CALL_STATUS_BUSY,
                    Call::TWILIO_DIAL_CALL_STATUS_CANCELED,
                    Call::TWILIO_DIAL_CALL_STATUS_COMPLETED,
                    Call::TWILIO_DIAL_CALL_STATUS_FAILED,
                    Call::TWILIO_DIAL_CALL_STATUS_NO_ANSWER,
                ]),
            ],

            RequestKeys::TWILIO_CALL_STATUS => [
                'required',
                Rule::in([
                    Call::TWILIO_CALL_STATUS_QUEUED,
                    Call::TWILIO_CALL_STATUS_RINGING,
                    Call::TWILIO_CALL_STATUS_IN_PROGRESS,
                    Call::TWILIO_CALL_STATUS_COMPLETED,
                    Call::TWILIO_CALL_STATUS_BUSY,
                    Call::TWILIO_CALL_STATUS_FAILED,
                    Call::TWILIO_CALL_STATUS_NO_ANSWER,
                    Call::TWILIO_CALL_STATUS_CANCELED,
                ]),
            ],
        ];
    }

    public function attributes()
    {
        return [
            RequestKeys::TWILIO_CALL_SID         => RequestKeys::TWILIO_CALL_SID,
            RequestKeys::TWILIO_CALL_STATUS      => RequestKeys::TWILIO_CALL_STATUS,
            RequestKeys::TWILIO_DIAL_CALL_STATUS => RequestKeys::TWILIO_DIAL_CALL_STATUS,
        ];
    }

    public function status(): string
    {
        switch ($this->request->get(RequestKeys::TWILIO_CALL_STATUS)) {
            case Call::TWILIO_CALL_STATUS_QUEUED:
            case Call::TWILIO_CALL_STATUS_RINGING:
            case Call::TWILIO_CALL_STATUS_IN_PROGRESS:

                return Call::STATUS_IN_PROGRESS;
            case Call::TWILIO_CALL_STATUS_COMPLETED:
            case Call::TWILIO_CALL_STATUS_FAILED:
            case Call::TWILIO_CALL_STATUS_NO_ANSWER:
            case Call::TWILIO_CALL_STATUS_CANCELED:

                return Call::STATUS_COMPLETED;
            default:

                return Call::STATUS_INVALID;
        }
    }

    public function dialStatus(): string
    {
        switch ($this->request->get(RequestKeys::TWILIO_DIAL_CALL_STATUS)) {
            case Call::TWILIO_DIAL_CALL_STATUS_ANSWERED:
            case Call::TWILIO_DIAL_CALL_STATUS_BUSY:
            case Call::TWILIO_DIAL_CALL_STATUS_NO_ANSWER:

                return Call::STATUS_IN_PROGRESS;
            case Call::TWILIO_DIAL_CALL_STATUS_COMPLETED:
            case Call::TWILIO_DIAL_CALL_STATUS_FAILED:
            case Call::TWILIO_DIAL_CALL_STATUS_CANCELED:

                return Call::STATUS_COMPLETED;
            default:

                return Call::STATUS_INVALID;
        }
    }

    public function callEnded(): bool
    {
        return Call::STATUS_COMPLETED == $this->status();
    }

    public function agentHungUp(): bool
    {
        return Call::STATUS_COMPLETED !== $this->status() && Call::STATUS_COMPLETED == $this->dialStatus();
    }

    public function call(): Call
    {
        return $this->callExists->call();
    }
}
