<?php

namespace App\Http\Requests\LiveApi\V2\Order\ItemOrder\CustomItem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::NAME     => ['required', 'string', 'min:3', 'max:40'],
            RequestKeys::QUANTITY => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
