<?php

namespace App\Models\Phone\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCountryCode implements Scope
{
    private int $countryCode;

    public function __construct(int $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('country_code', $this->countryCode);
    }
}
