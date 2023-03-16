<?php

namespace App\Models\Supply\Scopes;

use App\Models\CartSupplyCounter;
use App\Models\Supply;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LastAddedToCart implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $cartSupplyCounterTableName = CartSupplyCounter::tableName();
        $supplyTableName            = Supply::tableName();

        $builder->join("$cartSupplyCounterTableName", "$cartSupplyCounterTableName.supply_id", "$supplyTableName.id")
            ->groupBy("$cartSupplyCounterTableName.supply_id")
            ->select(DB::raw("$supplyTableName.*, max($cartSupplyCounterTableName.created_at) as added_at"))
            ->orderBy('added_at', 'desc');
    }
}
