<?php

namespace App\Http\Requests\Api\V3\User;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SEARCH_STRING => ['required', 'string', 'min:3'],
        ];
    }
}
