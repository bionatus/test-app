<?php

namespace App\Models\UserTaggable\Scopes;

use App\Models\IsTaggable;
use App\Models\Scopes\ByKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
        $builder->whereHasMorph('taggable', [$this->taggable->getMorphClass()], function(Builder $query) {
            $query->scoped(new ByKey($this->taggable->getKey()));
        });
    }
}
