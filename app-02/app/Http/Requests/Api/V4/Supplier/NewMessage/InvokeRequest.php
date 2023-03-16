<?php

namespace App\Http\Requests\Api\V4\Supplier\NewMessage;

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
