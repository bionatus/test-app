<?php

namespace App\Http\Requests\Api\V3\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Cart;
use App\Models\Item;
use App\Rules\CartItem\UniqueItem;
use Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Cart $cart */
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $rules[RequestKeys::ITEM] = [
            'required',
            'string',
            Rule::exists(Item::tableName(), Item::routeKeyName()),
            new UniqueItem($cart),
        ];

        $rules[RequestKeys::QUANTITY] = ['required', 'integer', 'min:1'];

        return $rules;
    }
}
