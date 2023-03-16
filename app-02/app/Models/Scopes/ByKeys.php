<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByKeys implements Scope
{
    private iterable $keys;

    public function __construct(iterable $keys)
    {
        $this->keys = $keys;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn($model->getKeyName(), $this->keys);
    }
}
