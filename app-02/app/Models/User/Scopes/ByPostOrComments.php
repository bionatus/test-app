<?php

namespace App\Models\User\Scopes;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByPostOrComments implements Scope
{
    private Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $post = $this->post;
        $builder->where(function(Builder $builder) use ($post) {
            $builder->whereHas('posts', function(Builder $builder) use ($post) {
                return $builder->where('id', $post->getKey());
            })->orWhereHas('comments', function(Builder $builder) use ($post) {
                return $builder->where('post_id', $post->getKey());
            });
        });
    }
}
