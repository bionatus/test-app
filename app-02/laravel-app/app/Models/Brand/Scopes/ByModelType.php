<?php

namespace App\Models\Brand\Scopes;

use App\Models\ModelType;
use App\Models\Series\Scopes\ByOemType;
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
            $builder->whereHas('series', function($query) {
                $query->scoped(new ByOemType($this->modelType));
            });
        }
    }
}
