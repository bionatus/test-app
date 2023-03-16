<?php

namespace App\Http\Requests\BasecampApi\V1\User;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\ArrayStringAllInteger;
use App\Rules\ArrayStringMax;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::USERS         => [
                'prohibited_unless:' . RequestKeys::SEARCH_STRING . ',null',
                'required_without:' . RequestKeys::SEARCH_STRING,
                'string',
                new ArrayStringMax(100),
                new ArrayStringAllInteger(),
            ],
            RequestKeys::SEARCH_STRING => [
                'prohibited_unless:' . RequestKeys::USERS . ',null',
                'required_without:' . RequestKeys::USERS,
                'string',
                'min:3',
            ],
        ];
    }
}
