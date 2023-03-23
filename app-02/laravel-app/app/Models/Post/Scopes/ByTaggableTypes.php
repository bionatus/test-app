<?php

namespace App\Models\Post\Scopes;

use App\Models\Tag\Scopes\ByTaggable;
use App\Types\TaggableType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Collection;

class ByTaggableTypes implements Scope
{
    private Collection $taggableTypes;

    public function __construct(Collection $taggableTypes)
    {
        $this->taggableTypes = $taggableTypes;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $this->taggableTypes->sortBy(function(TaggableType $taggableType) {
            return ($taggableType->connector() === TaggableType::CONNECTOR_AND) ? 1 : 2;
        })->each(function(TaggableType $taggableType) use ($builder) {
            $builder->has('tags', '>=', 1, $taggableType->connector(), function(Builder $builder) use ($taggableType) {
                $builder->scoped(new ByTaggable($taggableType->taggable()));
            });
        });
    }
}
