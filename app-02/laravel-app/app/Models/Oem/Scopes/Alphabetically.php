<?php

namespace App\Models\Oem\Scopes;

use App\Models\Scopes\Alphabetically as BaseAlphabetically;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Alphabetically implements Scope
{
    private string $attribute;

    public function __construct(string $attribute)
    {
        $this->attribute = $attribute;
    }

    public function apply(Builder $builder, Model $model): void
    {
        (new BaseAlphabetically($this->attribute))->apply($builder, $model);
    }
}
