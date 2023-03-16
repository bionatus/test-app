<?php

namespace App\Http\Requests\LiveApi\V1\Auth\Email\ResetPassword;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::TOKEN    => ['required'],
            RequestKeys::EMAIL    => ['required', 'string', 'bail', 'email:strict', 'ends_with_tld'],
            RequestKeys::PASSWORD => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
