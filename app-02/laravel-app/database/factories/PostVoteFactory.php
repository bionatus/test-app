<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PostVote create($attributes = [], ?Model $parent = null)
 * @method Collection|PostVote make($attributes = [], ?Model $parent = null)
 */
class PostVoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
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

    public function usingPost(Post $post): self
    {
        return $this->state(function() use ($post) {
            return [
                'post_id' => $post,
            ];
        });
    }
}
