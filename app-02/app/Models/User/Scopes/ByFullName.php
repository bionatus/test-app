<?php

namespace App\Models\User\Scopes;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByFullName implements Scope
{
    private string $searchString;

    public function __construct(string $searchString)
    {
        $this->searchString = $searchString;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(DB::raw("CONCAT(users.first_name,' ', users.last_name)"), 'LIKE', "%{$this->searchString}%");
    }
}
