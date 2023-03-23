<?php

namespace App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Item;
use App\Models\Wishlist;
use App\Rules\ItemWishlist\UniqueItem;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->route(RouteParameters::WISHLIST);

        $rules[RequestKeys::ITEM] = [
            'required',
            'string',
            Rule::exists(Item::tableName(), Item::routeKeyName()),
            new UniqueItem($wishlist),
        ];

        $rules[RequestKeys::QUANTITY] = ['required', 'integer', 'min:1'];

        return $rules;
    }
}
