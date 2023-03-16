<?php

namespace App\Models\Post\Scopes;

use App\Models\Tag;
use App\Types\TaggableType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Collection;

class TaggableTypesQuantity implements Scope
{
    private Collection $taggableTypes;

    public function __construct(Collection $taggableTypes)
    {
        $this->taggableTypes = $taggableTypes;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy(Tag::selectRaw('count(1) as q')->whereColumn('post_id', 'posts.id')->where(function(
            Builder $builder
        ) {
            $this->taggableTypes->each(function(TaggableType $taggableType) use ($builder) {
                $taggable = $taggableType->taggable();
                $builder->orWhere(function(Builder $builder) use ($taggable) {
                    $builder->where('taggable_type', $taggable->getMorphClass())
                        ->where('taggable_id', $taggable->getKey());
                });
            });
        }), 'desc');
    }
}
