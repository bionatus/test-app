<?php

namespace App\Models\User\Scopes;

use App\Models\Tag;
use App\Models\UserTaggable\Scopes\ByTaggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByFollowedTags implements Scope
{
    private Collection $tags;

    public function __construct(Collection $tags)
    {
        $this->tags = $tags;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('followedTags', function(Builder $query) {
            $query->where(function(Builder $query) {
                foreach ($this->tags as $tag) {
                    /** @var Tag $tag */
                    $query->orWhere(function(Builder $query) use ($tag) {
                        $query->scoped(new ByTaggable($tag->taggable));
                    });
                }
            });
        });
    }
}
