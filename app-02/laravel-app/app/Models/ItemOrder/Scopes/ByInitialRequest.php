<?php

namespace App\Models\ItemOrder\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByInitialRequest implements Scope
{
    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->where('initial_request', $this->value);
    }
}
