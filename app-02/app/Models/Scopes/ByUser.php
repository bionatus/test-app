<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByUser implements Scope
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('user_id', $this->user->getKey());
    }
}
