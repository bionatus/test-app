<?php

namespace App\Models\Tag\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;

class ByTypeId implements Scope
{
    private ?string $type;
    private ?string $id;

    public function __construct(?string $type, ?string $id)
    {
        $this->type = $type;
        $this->id   = $id;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->type && $this->id && Relation::getMorphedModel($this->type)) {
            $builder->where('taggable_type', $this->type);
            $builder->where('taggable_id', $this->id);
        }
    }
}
