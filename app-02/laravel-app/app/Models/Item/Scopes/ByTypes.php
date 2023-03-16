<?php

namespace App\Models\Item\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByTypes implements Scope
{
    private array $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn('type', $this->types);
    }
}
