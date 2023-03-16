<?php

namespace Database\Factories;

use App\Models\SupplySearchCounter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupplySearchCounter create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplySearchCounter createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|SupplySearchCounter make($attributes = [], ?Model $parent = null)
 */
class SupplySearchCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'     => $this->faker->unique()->uuid,
            'criteria' => $this->faker->text(255),
            'results'  => $this->faker->numberBetween(0, 999),
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

    public function withUser(): self
    {
        return $this->state(function() {
            return [
                'user_id' => User::factory(),
            ];
        });
    }
}
