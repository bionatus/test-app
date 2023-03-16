<?php

namespace App\Http\Requests\Api\V4\Order\ItemOrder\ExtraItem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\Item\UserCustomItemAndSupply;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $rules[RequestKeys::ITEMS]                 = ['required', 'array'];
        $rules[RequestKeys::ITEMS . '.*']          = ['required'];
        $rules[RequestKeys::ITEMS . '.*.uuid']     = [
            'required',
            'string',
            new UserCustomItemAndSupply(),
        ];
        $rules[RequestKeys::ITEMS . '.*.quantity'] = ['required', 'integer', 'min:1'];

        return $rules;
    }
}
