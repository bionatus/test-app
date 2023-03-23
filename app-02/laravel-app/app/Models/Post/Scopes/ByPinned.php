<?php

namespace App\Models\Post\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByPinned implements Scope
{
    private bool $pinned;

    public function __construct(bool $pinned)
    {
        $this->pinned = $pinned;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('pinned', '=', $this->pinned);
    }
}
