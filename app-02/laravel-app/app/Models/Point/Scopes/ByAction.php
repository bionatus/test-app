<?php

namespace App\Models\Point\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByAction implements Scope
{
    private string $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('action', $this->action);
    }
}
