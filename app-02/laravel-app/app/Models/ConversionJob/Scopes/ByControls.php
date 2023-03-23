<?php

namespace App\Models\ConversionJob\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByControls implements Scope
{
    private array $controls;

    public function __construct(array $controls)
    {
        $this->controls = $controls;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn('control', $this->controls);
    }
}
