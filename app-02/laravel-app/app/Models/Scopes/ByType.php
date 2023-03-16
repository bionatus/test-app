<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByType implements Scope
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('type', $this->type);
    }
}
