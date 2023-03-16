<?php

namespace App\Models\ItemOrder\Scopes;

use App\Models\Item;
use App\Models\Scopes\ByType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IsPart implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('item', function(Builder $subQuery) {
            $subQuery->scoped(new ByType(Item::TYPE_PART));
        });
    }
}
