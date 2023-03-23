<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByRouteKeys implements Scope
{
    private iterable $routeKeys;

    public function __construct(iterable $routeKeys)
    {
        $this->routeKeys = $routeKeys;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn($model->getRouteKeyName(), $this->routeKeys);
    }
}
