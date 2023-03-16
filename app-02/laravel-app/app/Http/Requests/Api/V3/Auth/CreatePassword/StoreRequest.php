<?php

namespace App\Http\Requests\Api\V3\Auth\CreatePassword;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::EMAIL => ['required', 'string', 'bail', 'email:strict', 'ends_with_tld'],
        ];
    }
}
