<?php

namespace App\Http\Requests\Api\V4\Order\Approve;

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
