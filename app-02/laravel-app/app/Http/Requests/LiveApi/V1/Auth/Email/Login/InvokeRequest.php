<?php

namespace App\Http\Requests\LiveApi\V1\Auth\Email\Login;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::EMAIL    => ['required', 'string', 'bail', 'email:strict', 'ends_with_tld'],
            RequestKeys::PASSWORD => ['required', 'string'],
        ];
    }
}
