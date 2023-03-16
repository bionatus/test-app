<?php

namespace App\Models\Level\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByLevelRange implements Scope
{
    private int $totalPointsEarned;

    public function __construct(int $totalPointsEarned)
    {
        $this->totalPointsEarned = $totalPointsEarned;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('from', '<=', $this->totalPointsEarned)->where(function($query) {
            $query->where('to', '>=', $this->totalPointsEarned)->orWhereNull('to');
        });
    }
}
