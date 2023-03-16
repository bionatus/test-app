<?php

namespace App\Models\Post\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PinnedFirst implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy('pinned', 'desc');
    }
}
