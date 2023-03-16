<?php

namespace App\Http\Requests\Api\V3\Twilio\Webhooks\Auth\Call;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\AuthenticationCode;
use App\Rules\Phone\FullNumberExist;

class InvokeRequest extends FormRequest
{
    private FullNumberExist $fullNumberExist;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->fullNumberExist = App::make(FullNumberExist::class);
    }

    public function rules(): array
    {
        return [
            RequestKeys::TWILIO_UPPER_TO => ['required', $this->fullNumberExist],
        ];
    }

    public function authenticationCode(): AuthenticationCode
    {
        return $this->fullNumberExist->authenticationCode();
    }
}
