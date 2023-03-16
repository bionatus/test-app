<?php

namespace App\Scopes;

use Illuminate\Database\Query\Builder;

interface Scope
{
    public function apply(Builder $builder);
}
