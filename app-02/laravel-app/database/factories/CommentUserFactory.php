<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CommentUser create($attributes = [], ?Model $parent = null)
 * @method Collection|CommentUser make($attributes = [], ?Model $parent = null)
 */
class CommentUserFactory extends Factory
{
    protected $model = CommentUser::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'comment_id' => Comment::factory(),
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingComment(Comment $comment): self
    {
        return $this->state(function() use ($comment) {
            return [
                'comment_id' => $comment,
            ];
        });
    }
}
