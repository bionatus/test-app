<?php

namespace App\Http\Requests\Api\V4\Order\Name;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::NAME => ['required', 'string', 'min:2', 'max:50'],
        ];
    }
}
