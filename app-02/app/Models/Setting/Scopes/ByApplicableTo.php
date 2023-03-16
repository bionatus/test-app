<?php

namespace App\Models\Setting\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByApplicableTo implements Scope
{
    private string $applicableTo;

    public function __construct(string $applicableTo)
    {
        $this->applicableTo = $applicableTo;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('applicable_to', $this->applicableTo);
    }
}
