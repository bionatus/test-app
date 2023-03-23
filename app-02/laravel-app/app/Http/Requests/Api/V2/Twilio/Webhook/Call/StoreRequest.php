<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Communication;
use App\Models\Scopes\ByRouteKey;
use App\Models\Subject;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    private User    $tech;
    private Subject $subject;

    protected function failedValidation(Validator $validator)
    {
        throw new StoreRequestValidationException($validator);
    }

    public function rules(): array
    {
        return [
            RequestKeys::TWILIO_LOWER_FROM => ['required', 'numeric', Rule::exists(User::tableName(), User::keyName())],
            RequestKeys::TWILIO_CALL_SID   => ['required', 'string'],
            RequestKeys::TWILIO_LOWER_TO   => ['required', Rule::exists(Subject::tableName(), Subject::routeKeyName())],
        ];
    }

    public function attributes()
    {
        return [
            RequestKeys::TWILIO_LOWER_FROM => RequestKeys::TWILIO_LOWER_FROM,
            RequestKeys::TWILIO_CALL_SID   => RequestKeys::TWILIO_CALL_SID,
            RequestKeys::TWILIO_LOWER_TO   => RequestKeys::TWILIO_LOWER_TO,
        ];
    }

    /**
     * @throws Exception
     */
    public function tech(): User
    {
        if (!isset($this->tech)) {
            throw new Exception('Can not access tech before validating the request');
        }

        return $this->tech;
    }

    /**
     * @throws Exception
     */
    public function subject(): Subject
    {
        if (!isset($this->subject)) {
            throw new Exception('Can not access subject before validating the request');
        }

        return $this->subject;
    }

    public function provider(): string
    {
        return Communication::PROVIDER_TWILIO;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function () use ($validator) {
            if (!$validator->failed()) {
                $this->tech    = User::find($this->get(RequestKeys::TWILIO_LOWER_FROM));
                $this->subject = Subject::scoped(new ByRouteKey($this->get(RequestKeys::TWILIO_LOWER_TO)))->first();
            }
        });
    }

    public function providerId(): string
    {
        return $this->get(RequestKeys::TWILIO_CALL_SID);
    }
}
