<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\LevelUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|LevelUser create($attributes = [], ?Model $parent = null)
 * @method Collection|LevelUser make($attributes = [], ?Model $parent = null)
 */
class LevelUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'user_id'  => User::factory(),
        ];
    }

    public function usingLevel(Level $level): self
    {
        return $this->state(function() use ($level) {
            return [
                'level_id' => $level,
            ];
        });
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
