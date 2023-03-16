<?php

namespace App\Models\ItemWishlist\Scopes;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByItem implements Scope
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('item_id', $this->item->getKey());
    }
}
