<?php

namespace App\Models\SupplierHour\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Collection;

class ExceptDays implements Scope
{
    private Collection $days;

    public function __construct(Collection $days)
    {
        $this->days = $days;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotIn('day', $this->days);
    }
}
