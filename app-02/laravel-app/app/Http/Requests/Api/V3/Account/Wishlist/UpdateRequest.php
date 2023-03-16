<?php

namespace App\Http\Requests\Api\V3\Account\Wishlist;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::NAME => ['required', 'string', 'max:255'],
        ];
    }
}
