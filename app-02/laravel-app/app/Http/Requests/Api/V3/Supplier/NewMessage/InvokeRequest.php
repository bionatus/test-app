<?php

namespace App\Http\Requests\Api\V3\Supplier\NewMessage;

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
