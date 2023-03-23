<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|CommentVote create($attributes = [], ?Model $parent = null)
 * @method Collection|CommentVote make($attributes = [], ?Model $parent = null)
 */
class CommentVoteFactory extends Factory
{
    protected   $model              = CommentVote::class;
    private int $incrementalSeconds = 0;

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

    public function withDecrementingCreationDate(): self
    {
        return $this->state(function() {
            return [
                'created_at' => Carbon::now()->subSeconds($this->incrementalSeconds++),
            ];
        });
    }
}
