<?php

namespace App\Models\Part\Scopes;

use App\Models\Scopes\ByRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByParentRouteKey implements Scope
{
    private string $routeKey;

    public function __construct(string $routeKey)
    {
        $this->routeKey = $routeKey;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('item', function(Builder $builder) {
            $builder->scoped(new ByRouteKey($this->routeKey));
        });
    }
}
