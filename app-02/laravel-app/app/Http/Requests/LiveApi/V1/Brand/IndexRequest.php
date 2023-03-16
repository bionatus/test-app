<?php

namespace App\Http\Requests\LiveApi\V1\Brand;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SEARCH_STRING => ['nullable', 'string', 'max:255'],
        ];
    }
}
