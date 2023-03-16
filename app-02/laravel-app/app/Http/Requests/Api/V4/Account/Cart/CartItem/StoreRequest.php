<?php

namespace App\Http\Requests\Api\V4\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Item;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $rules[RequestKeys::ITEMS]                 = ['required', 'array'];
        $rules[RequestKeys::ITEMS . '.*']          = ['required'];
        $rules[RequestKeys::ITEMS . '.*.uuid']     = [
            'required',
            'string',
            Rule::exists(Item::tableName(), Item::routeKeyName()),
        ];
        $rules[RequestKeys::ITEMS . '.*.quantity'] = ['required', 'integer', 'min:1'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            RequestKeys::ITEMS . '.*.uuid.exists' => 'Each item in items must exist.',
        ];
    }
}
