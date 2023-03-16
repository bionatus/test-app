<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByRouteKey implements Scope
{
    private string $routeKey;

    public function __construct(string $routeKey)
    {
        $this->routeKey = $routeKey;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getRouteKeyName(), $this->routeKey);
    }
}
