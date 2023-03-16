<?php

namespace Database\Factories;

use App\Models\Warning;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Warning create($attributes = [], ?Model $parent = null)
 * @method Collection|Warning make($attributes = [], ?Model $parent = null)
 */
class WarningFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => $this->faker->unique()->word,
            'description' => $this->faker->text(100),
        ];
    }
}
