<?php

namespace App\Http\Requests\Api\V3\Supply\Search;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::NAME => ['required', 'string', 'min:3', 'max:255'],
        ];
    }
}
