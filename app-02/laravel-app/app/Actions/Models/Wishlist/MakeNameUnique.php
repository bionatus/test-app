<?php

namespace App\Actions\Models\Wishlist;

use App;
use App\Models\User;

class MakeNameUnique
{
    public function execute(User $user, string $name): string
    {
        $userWishlists = $user->wishlists;
        $originalName  = $name;
        $i             = 1;
        while ($userWishlists->isNotEmpty() && $userWishlists->where('name', $name)->isNotEmpty()) {
            $name = $originalName . ' ' . ++$i;
        }

        return $name;
    }
}
