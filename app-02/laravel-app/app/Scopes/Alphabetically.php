<?php

namespace App\Scopes;

use Illuminate\Database\Query\Builder;

class Alphabetically implements Scope
{
    public function apply(Builder $builder): void
    {
        $builder->oldest('name');
    }
}
