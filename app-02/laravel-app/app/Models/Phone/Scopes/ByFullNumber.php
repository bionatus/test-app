<?php

namespace App\Models\Phone\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByFullNumber implements Scope
{
    private string $number;

    public function __construct(string $number)
    {
        $this->number = $number;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereRaw('CONCAT(country_code, number) = :number', ['number' => $this->number]);
    }
}
