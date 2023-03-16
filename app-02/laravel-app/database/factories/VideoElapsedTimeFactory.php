<?php

namespace Database\Factories;

use App\Models\VideoElapsedTime;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|VideoElapsedTime create($attributes = [], ?Model $parent = null)
 * @method Collection|VideoElapsedTime make($attributes = [], ?Model $parent = null)
 */
class VideoElapsedTimeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'version' => $this->faker->randomFloat(1, 0, 99999),
            'seconds' => $this->faker->numberBetween(0, 65535),
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
}
