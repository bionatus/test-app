<?php

namespace App\Http\Requests\LiveApi\V1\Order\InProgress;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::SEARCH_STRING => ['nullable', 'string', 'min:1'],
        ];
    }
}
