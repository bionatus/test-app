<?php

namespace App\Http\Requests\LiveApi\V1\Oem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::MODEL => ['required', 'string', 'min:3', 'max:200'],
        ];
    }
}
