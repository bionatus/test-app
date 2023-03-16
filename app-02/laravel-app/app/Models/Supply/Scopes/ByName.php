<?php

namespace App\Models\Supply\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByName implements Scope
{
    private string $supplyName;

    public function __construct(string $supplyName)
    {
        $this->supplyName = $supplyName;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('name', $this->supplyName);
    }
}
