<?php

namespace App\Models\Post\Scopes;

use App\Models\IsTaggable;
use App\Types\TaggablesCollection;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByAnyTaggable implements Scope
{
    private TaggablesCollection $taggables;

    public function __construct(TaggablesCollection $taggables)
    {
        $this->taggables = $taggables;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $concatenatedTypeAndIds = $this->taggables->map(function(IsTaggable $taggable) {
            return $taggable->getMorphClass() . '-' . $taggable->getKey();
        });

        $builder->whereHas('tags', function(Builder $builder) use ($concatenatedTypeAndIds) {
            $builder->whereIn(DB::raw('CONCAT(taggable_type,\'-\',taggable_id)'), $concatenatedTypeAndIds->toArray());
        });
    }
}
