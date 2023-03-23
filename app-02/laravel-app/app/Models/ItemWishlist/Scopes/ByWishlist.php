<?php

namespace App\Models\ItemWishlist\Scopes;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByWishlist implements Scope
{
    private Wishlist $wishlist;

    public function __construct(Wishlist $wishlist)
    {
        $this->wishlist = $wishlist;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('wishlist_id', $this->wishlist->getKey());
    }
}
