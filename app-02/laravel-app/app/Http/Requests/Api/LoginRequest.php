<?php

namespace App\Http\Requests\Api;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::DEVICE  => ['string', 'min:10', 'max:255'],
            RequestKeys::VERSION => [
                'required_with:' . RequestKeys::DEVICE,
                'string',
                'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/'],
        ];
    }
}
