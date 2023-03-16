<?php

namespace App\Models\ServiceToken\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByTokenName implements Scope
{
    private string $tokenName;

    public function __construct(string $tokenName)
    {
        $this->tokenName = $tokenName;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('token_name', '=', $this->tokenName);
    }
}
