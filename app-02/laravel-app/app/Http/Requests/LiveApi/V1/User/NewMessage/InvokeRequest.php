<?php

namespace App\Http\Requests\LiveApi\V1\User\NewMessage;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::MESSAGE => ['required', 'string'],
        ];
    }
}
