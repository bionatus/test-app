<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NearZipCodes implements Scope
{
    private string $zipCode;

    public function __construct(string $zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByRaw("IF(zip_code = ? , 1, 0 ) DESC ", [$this->zipCode]);
    }
}
