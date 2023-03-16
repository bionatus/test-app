<?php

namespace App\Models\Activity\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCauser implements Scope
{
    private Model $causer;

    public function __construct(Model $causer)
    {
        $this->causer = $causer;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('causer_id', $this->causer->getKey())->where('causer_type', $this->causer->getMorphClass());
    }
}
