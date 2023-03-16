<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByZipCode implements Scope
{
    private string $zipCode;

    public function __construct(string $zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('zip_code', $this->zipCode);
    }
}
