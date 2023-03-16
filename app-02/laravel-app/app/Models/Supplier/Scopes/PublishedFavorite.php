<?php

namespace App\Models\Supplier\Scopes;

use App\Models\Scopes\ByUser;
use App\Models\SupplierUser\Scopes\ByVisibleByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublishedFavorite implements Scope
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->withExists([
            'supplierUsers as favorite' => function(Builder $query) {
                $query->scoped(new ByUser($this->user))->scoped(new ByVisibleByUser(true));
            },
        ])->orderByDesc('favorite')->orderByRaw('IF((favorite = 1 and published_at IS NOT NULL), 1, 0 ) DESC');
    }
}
