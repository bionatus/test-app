<?php

namespace App\Rules\ItemWishlist;

use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\ItemWishlist\Scopes\ByItem;
use App\Models\ItemWishlist\Scopes\ByWishlist;
use App\Models\Scopes\ByRouteKey;
use App\Models\Wishlist;
use Illuminate\Contracts\Validation\Rule;

class UniqueItem implements Rule
{
    private Wishlist $wishlist;

    public function __construct(Wishlist $wishlist)
    {
        $this->wishlist = $wishlist;
    }

    public function passes($attribute, $value): bool
    {
        /** @var Item $item */
        $item = Item::scoped(new ByRouteKey($value))->first();

        if ($item && ItemWishlist::scoped(new ByWishlist($this->wishlist))->scoped(new ByItem($item))->doesntExist()) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return 'This :attribute already exists on the wishlist';
    }
}
