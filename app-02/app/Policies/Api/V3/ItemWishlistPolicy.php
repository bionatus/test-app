<?php

namespace App\Policies\Api\V3;

use App\Models\ItemWishlist;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemWishlistPolicy
{
    use HandlesAuthorization;

    public function update(User $user, ItemWishlist $itemWishlist): bool
    {
        return $itemWishlist->wishlist->isOwner($user);
    }

    public function delete(User $user, ItemWishlist $itemWishlist): bool
    {
        return $itemWishlist->wishlist->isOwner($user);
    }
}
