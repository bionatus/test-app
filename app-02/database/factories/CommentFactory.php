<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Comment create($attributes = [], ?Model $parent = null)
 * @method Collection|Comment make($attributes = [], ?Model $parent = null)
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'post_id'  => Post::factory(),
            'uuid'     => $this->faker->unique()->uuid,
            'message'  => $this->faker->text($this->faker->numberBetween(100, 999)),
            'solution' => null,
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

    public function solution(): self
    {
        return $this->state(function() {
            return [
                'solution' => true,
            ];
        });
    }
}
