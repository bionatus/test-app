<?php

namespace App\Models\User\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByEnabled implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull('disabled_at');
    }
}
