<?php

namespace App\Models\Oem\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByModel implements Scope
{
    private ?string $searchModel;

    public function __construct(?string $searchModel)
    {
        $this->searchModel = $searchModel;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->searchModel) {
            $builder->where('model', 'LIKE', "%{$this->searchModel}%");
        }
    }
}
