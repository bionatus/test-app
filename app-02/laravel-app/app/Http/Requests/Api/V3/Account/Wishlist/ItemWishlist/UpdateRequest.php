<?php

namespace App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist;

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
