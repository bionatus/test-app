<?php

namespace App\Models\AuthenticationCode\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCode implements Scope
{
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('code', $this->code);
    }
}
