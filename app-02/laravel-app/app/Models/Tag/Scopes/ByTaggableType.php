<?php

namespace App\Models\Tag\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByTaggableType implements Scope
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('taggable_type', $this->type);
    }
}
