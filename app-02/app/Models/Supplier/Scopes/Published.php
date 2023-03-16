<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Published implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByRaw('published_at IS NULL');
    }
}
