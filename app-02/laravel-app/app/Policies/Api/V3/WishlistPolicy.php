<?php

namespace App\Policies\Api\V3;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Auth\Access\HandlesAuthorization;

class WishlistPolicy
{
    use HandlesAuthorization;

    public function read(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->isOwner($user);
    }
}
