<?php

namespace App\Rules\CartItem;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Scopes\ByUuid;
use Illuminate\Contracts\Validation\Rule;

class UniqueItem implements Rule
{
    private Cart $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function passes($attribute, $value): bool
    {
        /** @var Item $item */
        $item = Item::scoped(new ByUuid($value))->first();

        if ($item && CartItem::where('cart_id', $this->cart->id)->where('item_id', $item->id)->doesntExist()) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return 'This :attribute already exists on the cart';
    }
}
