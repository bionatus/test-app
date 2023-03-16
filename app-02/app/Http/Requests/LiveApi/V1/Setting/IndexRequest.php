<?php

namespace App\Http\Requests\LiveApi\V1\Setting;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::GROUP => ['nullable', 'string', 'max:255'],
        ];
    }
}
