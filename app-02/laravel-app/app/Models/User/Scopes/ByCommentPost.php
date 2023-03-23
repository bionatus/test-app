<?php

namespace App\Models\User\Scopes;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCommentPost implements Scope
{
    private Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $post = $this->post;
        $builder->whereExists(function($query) use ($post) {
            $query->select('comments.user_id')
                ->from('comments')
                ->where('post_id', $post->id)
                ->whereColumn('comments.user_id', 'users.id');
        });
    }
}
