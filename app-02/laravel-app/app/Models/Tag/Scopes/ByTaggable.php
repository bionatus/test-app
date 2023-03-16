<?php

namespace App\Models\Tag\Scopes;

use App\Models\IsTaggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Scope;

class ByTaggable implements Scope
{
    private IsTaggable $taggable;

    public function __construct(IsTaggable $taggable)
    {
        $this->taggable = $taggable;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $taggableType = Relation::getAliasByModel(get_class($this->taggable));
        $builder->scoped(new ByTypeId($taggableType, $this->taggable->getKey()));
        $builder->whereHasMorph('taggable', $taggableType, function(Builder $builder) {
        });
    }
}
