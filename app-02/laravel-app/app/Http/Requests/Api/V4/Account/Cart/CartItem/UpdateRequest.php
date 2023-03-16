<?php

namespace App\Http\Requests\Api\V4\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules[RequestKeys::QUANTITY] = ['required', 'integer', 'min:1'];

        return $rules;
    }
}
