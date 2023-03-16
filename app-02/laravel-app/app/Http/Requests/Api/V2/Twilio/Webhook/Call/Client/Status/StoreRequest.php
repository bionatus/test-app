<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Rules\Agent\Exists as AgentExists;
use App\Rules\AgentCall\Exists as AgentCallExists;
use App\Rules\Call\Exists as CallExists;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    private CallExists      $callExists;
    private AgentCallExists $agentCallExists;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $this->callExists      = App::make(CallExists::class, ['provider' => Communication::PROVIDER_TWILIO]);
        $this->agentCallExists = App::make(AgentCallExists::class, ['callExists' => $this->callExists]);

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
            RequestKeys::TWILIO_PARENT_CALL_SID => ['required', 'string', 'bail', $this->callExists],
            RequestKeys::TWILIO_UPPER_TO        => [
                'required',
                new AgentExists(),
                $this->agentCallExists,
            ],
            RequestKeys::TWILIO_CALL_STATUS     => [
                'required',
                Rule::in([
                    Call::TWILIO_CALL_STATUS_QUEUED,
                    Call::TWILIO_CALL_STATUS_INITIATED,
                    Call::TWILIO_CALL_STATUS_RINGING,
                    Call::TWILIO_CALL_STATUS_IN_PROGRESS,
                    Call::TWILIO_CALL_STATUS_BUSY,
                    Call::TWILIO_CALL_STATUS_FAILED,
                    Call::TWILIO_CALL_STATUS_NO_ANSWER,
                    Call::TWILIO_CALL_STATUS_COMPLETED,
                ]),
            ],
        ];
    }

    public function attributes()
    {
        return [
            RequestKeys::TWILIO_PARENT_CALL_SID => RequestKeys::TWILIO_PARENT_CALL_SID,
            RequestKeys::TWILIO_UPPER_TO        => RequestKeys::TWILIO_UPPER_TO,
            RequestKeys::TWILIO_CALL_STATUS     => RequestKeys::TWILIO_CALL_STATUS,
        ];
    }

    public function agentCall(): AgentCall
    {
        return $this->agentCallExists->agentCall();
    }

    public function status(): string
    {
        switch ($this->request->get(RequestKeys::TWILIO_CALL_STATUS)) {
            case Call::TWILIO_CALL_STATUS_QUEUED:
            case Call::TWILIO_CALL_STATUS_INITIATED:
            case Call::TWILIO_CALL_STATUS_RINGING:
                return AgentCall::STATUS_RINGING;

            case Call::TWILIO_CALL_STATUS_IN_PROGRESS:
                return AgentCall::STATUS_IN_PROGRESS;

            case Call::TWILIO_CALL_STATUS_BUSY:
            case Call::TWILIO_CALL_STATUS_FAILED:
            case Call::TWILIO_CALL_STATUS_NO_ANSWER:
                return AgentCall::STATUS_DROPPED;

            case Call::TWILIO_CALL_STATUS_COMPLETED:
                return AgentCall::STATUS_COMPLETED;

            default:
                return AgentCall::STATUS_INVALID;
        }
    }
}
