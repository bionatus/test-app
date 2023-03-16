<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByStatus implements Scope
{
    private string $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->where('status', $this->status);
    }
}
