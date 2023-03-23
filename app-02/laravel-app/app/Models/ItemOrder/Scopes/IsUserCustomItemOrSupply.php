<?php

namespace App\Models\ItemOrder\Scopes;

use App\Models\Item;
use App\Models\Scopes\ByCreatorType;
use App\Models\Scopes\ByType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IsUserCustomItemOrSupply implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('item', function(Builder $builder) {
            $builder->scoped(new ByType(Item::TYPE_CUSTOM_ITEM))->whereHas('customItem', function(Builder $builder) {
                $builder->scoped(new ByCreatorType(User::MORPH_ALIAS));
            })->orWhere(function(Builder $builder) {
                $builder->scoped(new ByType(Item::TYPE_SUPPLY));
            });
        });
    }
}
