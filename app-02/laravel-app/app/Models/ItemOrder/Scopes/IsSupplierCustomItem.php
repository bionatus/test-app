<?php

namespace App\Models\ItemOrder\Scopes;

use App\Models\Item;
use App\Models\Scopes\ByCreatorType;
use App\Models\Scopes\ByType;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IsSupplierCustomItem implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('item', function(Builder $subQuery) {
            $subQuery->scoped(new ByType(Item::TYPE_CUSTOM_ITEM))->whereHas('customItem', function(Builder $subQuery) {
                $subQuery->scoped(new ByCreatorType(Supplier::MORPH_ALIAS));
            });
        });
    }
}
