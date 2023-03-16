<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Device create($attributes = [], ?Model $parent = null)
 * @method Collection|Device make($attributes = [], ?Model $parent = null)
 */
class DeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'udid'        => $this->faker->unique()->word,
            'app_version' => $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0,
                    10) . '.' . $this->faker->numberBetween(0, 10),
            'user_id'     => User::factory(),
            'token'       => $this->faker->word,
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

    public function appVersion(string $version): self
    {
        return $this->state(function() use ($version) {
            return [
                'app_version' => $version,
            ];
        });
    }
}
