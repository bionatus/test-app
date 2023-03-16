<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExceptKeys implements Scope
{
    private array $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotIn($model->getKeyName(), $this->ids);
    }
}
