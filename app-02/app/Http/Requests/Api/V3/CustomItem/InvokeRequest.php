<?php

namespace App\Http\Requests\Api\V3\CustomItem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::NAME => ['required', 'string', 'min:2', 'max:40'],
        ];
    }
}
