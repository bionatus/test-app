<?php

namespace App\Http\Requests\LiveApi\V1\Brand\Series\Oem;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::SEARCH_STRING => ['nullable', 'string', 'min:3', 'max:200'],
        ];
    }
}
