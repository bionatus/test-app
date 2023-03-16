<?php

namespace App\Http\Requests\Api\V2\Twilio\Webhook\Call\Complete;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Call;
use App\Models\Communication;
use App\Rules\Call\Exists;
use Illuminate\Contracts\Validation\Validator;

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
        throw new StoreRequestValidationException($validator);
    }

    public function rules(): array
    {
        return [
            RequestKeys::TWILIO_CALL_SID => ['required', 'string', 'bail', $this->callExists],
        ];
    }

    public function attributes()
    {
        return [
            RequestKeys::TWILIO_CALL_SID => RequestKeys::TWILIO_CALL_SID,
        ];
    }

    public function call(): Call
    {
        return $this->callExists->call();
    }
}
