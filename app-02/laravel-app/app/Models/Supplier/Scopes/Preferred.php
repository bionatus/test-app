<?php

namespace App\Models\Supplier\Scopes;

use App\Models\Scopes\ByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Preferred implements Scope
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->withExists([
            'supplierUsers as preferred_supplier' => function(Builder $query) {
                $query->scoped(new ByUser($this->user))->scoped(new ByPreferred());
            },
        ])->orderByDesc('preferred_supplier');
    }
}
