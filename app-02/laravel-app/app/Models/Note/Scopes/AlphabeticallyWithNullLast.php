<?php

namespace App\Models\Note\Scopes;

use App\Models\Scopes\AlphabeticallyWithNullLast as BaseAlphabeticallyWithNullLast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AlphabeticallyWithNullLast implements Scope
{
    private string $attribute;

    public function __construct(string $attribute)
    {
        $this->attribute = $attribute;
    }

    public function apply(Builder $builder, Model $model): void
    {
        (new BaseAlphabeticallyWithNullLast($this->attribute))->apply($builder, $model);
    }
}
