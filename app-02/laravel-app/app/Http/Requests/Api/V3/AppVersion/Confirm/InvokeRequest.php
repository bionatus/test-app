<?php

namespace App\Http\Requests\Api\V3\AppVersion\Confirm;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::SECONDS => ['required', 'integer'],
        ];
    }
}
