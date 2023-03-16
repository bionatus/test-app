<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExceptKey implements Scope
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getKeyName(), '<>', $this->id);
    }
}
