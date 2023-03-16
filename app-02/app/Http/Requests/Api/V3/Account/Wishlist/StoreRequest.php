<?php

namespace App\Http\Requests\Api\V3\Account\Wishlist;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $rules[RequestKeys::NAME] = ['required', 'string', 'max:255'];

        return $rules;
    }
}
