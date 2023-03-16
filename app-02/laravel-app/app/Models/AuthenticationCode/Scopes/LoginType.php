<?php

namespace App\Models\AuthenticationCode\Scopes;

use App\Models\AuthenticationCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LoginType implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('type', AuthenticationCode::TYPE_LOGIN);
    }
}
