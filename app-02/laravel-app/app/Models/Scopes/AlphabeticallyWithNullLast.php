<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AlphabeticallyWithNullLast implements Scope
{
    private string $field;

    public function __construct(string $field = 'name')
    {
        $this->field = $field;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByRaw($this->field . " IS NULL")->scoped(new Alphabetically($this->field));
    }
}
