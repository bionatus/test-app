<?php

namespace App\Models\Activity\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByUserRelatedActivity implements Scope
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('relatedActivity', function(Builder $builder) {
            $builder->where('user_id', $this->user->getKey());
        });
    }
}
