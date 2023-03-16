<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Agent create($attributes = [], ?Model $parent = null)
 * @method Collection|Agent make($attributes = [], ?Model $parent = null)
 */
class AgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'   => User::factory(),
            'uuid' => $this->faker->unique()->uuid,
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'id' => $user,
            ];
        });
    }
}
