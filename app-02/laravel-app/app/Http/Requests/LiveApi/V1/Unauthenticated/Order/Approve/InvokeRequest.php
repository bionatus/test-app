<?php

namespace App\Http\Requests\LiveApi\V1\Unauthenticated\Order\Approve;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::NAME => ['nullable', 'bail', 'string', 'max:50'],
        ];
    }
}
