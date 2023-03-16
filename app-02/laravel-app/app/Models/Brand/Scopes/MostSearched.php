<?php

namespace App\Models\Brand\Scopes;

use App\Models\Brand;
use App\Models\BrandDetailCounter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MostSearched implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy(BrandDetailCounter::select(DB::raw('count(*) as total'))
            ->whereColumn('brand_id', Brand::tableName() . '.id')
            ->groupBy('brand_id'), 'desc');
    }
}
