<?php

namespace App\Models\Phone\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByNumber implements Scope
{
    private int $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('number', $this->number);
    }
}
