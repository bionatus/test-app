<?php

namespace App\Models\User\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByFullOrPublicName implements Scope
{
    private string $searchString;

    public function __construct(string $searchString)
    {
        $this->searchString = $searchString;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(function(Builder $builder) {
            $builder->scoped(new ByFullName($this->searchString))
                ->orWhere('users.public_name', 'LIKE', "%{$this->searchString}%");
        });
    }
}
