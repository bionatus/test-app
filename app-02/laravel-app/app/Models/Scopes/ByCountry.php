<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCountry implements Scope
{
    private string $country;

    public function __construct(string $country)
    {
        $this->country = $country;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('country', $this->country);
    }
}
