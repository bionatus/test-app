<?php

namespace App\Http\Requests\Api\V3\Oem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::MODEL => ['required', 'string', 'min:3', 'max:200'],
        ];
    }
}
