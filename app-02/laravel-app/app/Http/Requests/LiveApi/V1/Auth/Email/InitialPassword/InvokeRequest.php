<?php

namespace App\Http\Requests\LiveApi\V1\Auth\Email\InitialPassword;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::PASSWORD     => ['required', 'string', 'min:8', 'confirmed'],
            RequestKeys::TOS_ACCEPTED => ['required', 'accepted'],
        ];
    }
}
