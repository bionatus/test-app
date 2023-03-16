<?php

namespace App\Models\Supplier\Scopes;

use App\Models\Scopes\ByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InvitationSent implements Scope
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->withExists([
            'supplierInvitations as invitation_sent' => function(Builder $query) {
                $query->scoped(new ByUser($this->user));
            },
        ]);
    }
}
