<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByEmail implements Scope
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('email', $this->email);
    }
}
