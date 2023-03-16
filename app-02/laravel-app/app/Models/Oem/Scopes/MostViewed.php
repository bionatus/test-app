<?php

namespace App\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\OemDetailCounter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MostViewed implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy(OemDetailCounter::select(DB::raw('count(*) as total'))
            ->whereColumn('oem_id', Oem::tableName() . '.id')
            ->groupBy('oem_id'), 'desc');
    }
}
