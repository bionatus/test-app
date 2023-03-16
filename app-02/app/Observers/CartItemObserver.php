<?php

namespace App\Observers;

use App\Models\CartItem;
use Illuminate\Support\Str;

class CartItemObserver
{
    public function creating(CartItem $cartItem): void
    {
        $cartItem->uuid ??= Str::uuid();
    }
}
