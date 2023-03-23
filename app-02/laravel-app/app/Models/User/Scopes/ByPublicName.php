<?php

namespace App\Models\User\Scopes;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByPublicName implements Scope
{
    private string $searchString;

    public function __construct(string $searchString)
    {
        $this->searchString = $searchString;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('users.public_name', 'LIKE', "%{$this->searchString}%");
    }
}
