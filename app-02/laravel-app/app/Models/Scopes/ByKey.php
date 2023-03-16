<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByKey implements Scope
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getKeyName(), $this->key);
    }
}
