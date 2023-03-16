<?php

namespace App\Models\Scopes;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCreatedBefore implements Scope
{
    private CarbonInterface $date;

    public function __construct(CarbonInterface $date)
    {
        $this->date = $date;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('created_at', '<', $this->date);
    }
}
