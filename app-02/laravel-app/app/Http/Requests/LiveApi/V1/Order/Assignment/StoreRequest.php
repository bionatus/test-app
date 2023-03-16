<?php

namespace App\Http\Requests\LiveApi\V1\Order\Assignment;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::NAME => ['required', 'string', 'max:255'],
        ];
    }
}
