<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByOnTheNetwork implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotNull('verified_at')->whereNotNull('published_at');
    }
}
