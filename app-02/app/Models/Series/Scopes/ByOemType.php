<?php

namespace App\Models\Series\Scopes;

use App\Models\ModelType;
use App\Models\Oem\Scopes\ByModelType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByOemType implements Scope
{
    private ?ModelType $modelType;

    public function __construct(?ModelType $modelType)
    {
        $this->modelType = $modelType;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->modelType) {
            $builder->whereHas('oems', function($query) {
                $query->scoped(new ByModelType($this->modelType));
            });
        }
    }
}
