<?php

namespace App\Models\Supply\Scopes;

use App\Models\CartSupplyCounter;
use App\Models\Supply;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MostAddedToCart implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy(CartSupplyCounter::select(DB::raw('count(*) as total'))
            ->whereColumn('supply_id', Supply::tableName() . '.id')
            ->groupBy('supply_id'), 'desc');
    }
}
