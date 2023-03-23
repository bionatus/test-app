<?php

namespace App\Models\Post\Scopes;

use App\Models\Comment\Scopes\Solution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SolvedFirst implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->withCount([
            'comments as solved' => function(Builder $query) {
                $query->scoped(new Solution());
            },
        ])->orderBy('solved', 'desc');
    }
}
