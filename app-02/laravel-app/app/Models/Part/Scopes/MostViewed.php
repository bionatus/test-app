<?php

namespace App\Models\Part\Scopes;

use App\Models\Part;
use App\Models\PartDetailCounter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MostViewed implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy(PartDetailCounter::select(DB::raw('count(*) as total'))
            ->whereColumn('part_id', Part::tableName() . '.id')
            ->groupBy('part_id'), 'desc');
    }
}
