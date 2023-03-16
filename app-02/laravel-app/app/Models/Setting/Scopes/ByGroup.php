<?php

namespace App\Models\Setting\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByGroup implements Scope
{
    private string $group;

    public function __construct(string $group)
    {
        $this->group = $group;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('group', $this->group);
    }
}
