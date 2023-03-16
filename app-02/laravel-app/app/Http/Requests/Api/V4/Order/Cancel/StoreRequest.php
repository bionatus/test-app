<?php

namespace App\Http\Requests\Api\V4\Order\Cancel;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::STATUS_DETAIL => ['bail', 'required', 'string', 'min:5', 'max:255'],
        ];
    }
}
