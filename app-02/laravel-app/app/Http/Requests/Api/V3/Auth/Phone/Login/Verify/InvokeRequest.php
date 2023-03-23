<?php

namespace App\Http\Requests\Api\V3\Auth\Phone\Login\Verify;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::CODE    => ['required', 'integer', 'digits:6'],
            RequestKeys::DEVICE  => ['required', 'string', 'min:10', 'max:255'],
            RequestKeys::VERSION => [
                'required',
                'string',
                'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/',
            ],
        ];
    }
}
