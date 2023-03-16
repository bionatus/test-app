<?php

namespace App\Models\Wishlist\Scopes;

use App\Models\Scopes\ByUser as BaseByUser;
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
        (new BaseByUser($this->user))->apply($builder, $model);
    }
}
