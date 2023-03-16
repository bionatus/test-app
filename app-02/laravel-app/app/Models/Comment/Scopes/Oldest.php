<?php

namespace App\Models\Comment\Scopes;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Oldest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->oldest(Comment::CREATED_AT)->oldest(Comment::keyName());
    }
}
