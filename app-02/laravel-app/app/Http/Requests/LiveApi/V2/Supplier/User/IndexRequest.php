<?php

namespace App\Http\Requests\LiveApi\V2\Supplier\User;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SEARCH_STRING => ['nullable', 'string', 'min:2', 'max:50'],
        ];
    }
}
