<?php

namespace App\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\OemDetailCounter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LastViewed implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $oemDetailCounterTableName = OemDetailCounter::tableName();
        $oemTableName              = Oem::tableName();

        $builder->join("$oemDetailCounterTableName", "$oemDetailCounterTableName.oem_id", "$oemTableName.id")
            ->groupBy("$oemDetailCounterTableName.oem_id")
            ->select(DB::raw("$oemTableName.*, max($oemDetailCounterTableName.created_at) as visited_at"))
            ->orderBy('visited_at', 'desc');
    }
}
