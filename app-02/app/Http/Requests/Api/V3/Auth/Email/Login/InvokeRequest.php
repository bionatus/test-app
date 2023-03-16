<?php

namespace App\Http\Requests\Api\V3\Auth\Email\Login;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::EMAIL    => ['required', 'string'],
            RequestKeys::PASSWORD => ['required', 'string'],
            RequestKeys::DEVICE   => ['required', 'string', 'min:10', 'max:255'],
            RequestKeys::VERSION  => [
                'required',
                'string',
                'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/',
            ],
        ];
    }
}
