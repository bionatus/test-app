<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByPublished implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(function($query) {
            $query->whereNull('verified_at')->orWhere(function($nestedQuery) {
                $nestedQuery->whereNotNull('verified_at') && $nestedQuery->whereNotNull('published_at');
            });
        });
    }
}
