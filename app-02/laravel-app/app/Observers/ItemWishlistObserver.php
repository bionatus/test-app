<?php

namespace App\Observers;

use App\Models\ItemWishlist;
use Illuminate\Support\Str;

class ItemWishlistObserver
{
    public function creating(ItemWishlist $itemWishlist): void
    {
        $itemWishlist->uuid ??= Str::uuid();
    }
}
