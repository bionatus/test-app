<?php

namespace App\Http\Requests\Api\V2\Support\Ticket\AgentRate;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::RATING  => ['required', 'integer', 'min:1', 'max:5'],
            RequestKeys::COMMENT => ['nullable', 'string', 'max:400'],
        ];
    }
}
