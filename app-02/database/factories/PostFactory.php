<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Post create($attributes = [], ?Model $parent = null)
 * @method Collection|Post make($attributes = [], ?Model $parent = null)
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'uuid'    => $this->faker->unique()->uuid,
            'message' => $this->faker->text($this->faker->numberBetween(100, 999)),
            'type'    => Post::TYPE_OTHER,
            'pinned'  => false,
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

    public function needsHelp(): self
    {
        return $this->state(function() {
            return [
                'type' => Post::TYPE_NEEDS_HELP,
            ];
        });
    }

    public function funny(): self
    {
        return $this->state(function() {
            return [
                'type' => Post::TYPE_FUNNY,
            ];
        });
    }

    public function other(): self
    {
        return $this->state(function() {
            return [
                'type' => Post::TYPE_OTHER,
            ];
        });
    }

    public function pinned(): self
    {
        return $this->state(function() {
            return [
                'pinned' => true,
            ];
        });
    }
}
