<?php

namespace App\Http\Requests\Api\V3\Supplier;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::SEARCH_STRING => ['nullable', 'string', 'max:1000'],
            RequestKeys::ZIP_CODE      => ['nullable', 'string', 'bail', 'digits:5'],
        ];
    }
}
