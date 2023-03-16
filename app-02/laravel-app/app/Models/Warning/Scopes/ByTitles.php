<?php

namespace App\Models\Warning\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByTitles implements Scope
{
    private array $titles;

    public function __construct(array $titles)
    {
        $this->titles = $titles;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn('title', $this->titles);
    }
}
