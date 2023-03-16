<?php

namespace App\Models\Oem\Scopes;

use App\Models\ModelType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByModelType implements Scope
{
    private ?ModelType $modelType;

    public function __construct(?ModelType $modelType)
    {
        $this->modelType = $modelType;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->modelType) {
            $builder->where('model_type_id', $this->modelType->id);
        }
    }
}
