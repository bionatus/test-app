<?php

namespace App\Scopes\ModelType;

use App\Scopes\Scope;
use Illuminate\Database\Query\Builder;

class BySeriesKey implements Scope
{
    private string $seriesRouteKey;

    public function __construct(string $seriesRouteKey)
    {
        $this->seriesRouteKey = $seriesRouteKey;
    }

    public function apply(Builder $builder): void
    {
        $builder->leftJoin('oems', 'oems.model_type_id', '=', 'model_types.id')
            ->where('oems.series_id', $this->seriesRouteKey);
    }
}
