<?php

namespace App\Models\Part\Scopes;

use App\Models\Part;
use App\Models\PartDetailCounter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LastViewed implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $partDetailCounterTableName = PartDetailCounter::tableName();
        $partTableName              = Part::tableName();

        $builder->join("$partDetailCounterTableName", "$partDetailCounterTableName.part_id", "$partTableName.id")
            ->groupBy("$partDetailCounterTableName.part_id")
            ->select(DB::raw("$partTableName.*, max($partDetailCounterTableName.created_at) as visited_at"))
            ->orderBy('visited_at', 'desc');
    }
}
