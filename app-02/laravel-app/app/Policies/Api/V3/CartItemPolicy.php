<?php

namespace App\Policies\Api\V3;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartItemPolicy
{
    use HandlesAuthorization;

    public function update(User $user, CartItem $cartItem): bool
    {
        return $cartItem->cart->isOwner($user);
    }

    public function delete(User $user, CartItem $cartItem): bool
    {
        return $cartItem->cart->isOwner($user);
    }
}
