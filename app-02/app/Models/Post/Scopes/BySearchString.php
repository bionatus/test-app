<?php

namespace App\Models\Post\Scopes;

use App\Models\Scopes\ByName;
use App\Models\User\Scopes\ByFullOrPublicName;
use App\Models\User\Scopes\ByPublicName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BySearchString implements Scope
{
    private ?string $searchString;

    public function __construct(?string $searchString)
    {
        $this->searchString = $searchString;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->searchString) {
            $builder->where('message', 'LIKE', "%{$this->searchString}%");
            $builder->orWhereHas('user', function(Builder $query) {
                $query->scoped(new ByPublicName($this->searchString));
            });
            $builder->orWhereHas('tags', function(Builder $query) {
                $query->whereHas('taggable', function(Builder $subQuery) {
                    $subQuery->scoped(new ByName($this->searchString));
                });
            });
        }
    }
}
