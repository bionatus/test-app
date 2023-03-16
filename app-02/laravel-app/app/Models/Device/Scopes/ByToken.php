<?php

namespace App\Models\Device\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByToken implements Scope
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->where('token', $this->token);
    }
}
