<?php

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Level create($attributes = [], ?Model $parent = null)
 * @method Collection|Level make($attributes = [], ?Model $parent = null)
 */
class LevelFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->name;

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'from'        => $this->faker->numberBetween(0, 99),
            'to'          => $this->faker->numberBetween(100, 999),
            'coefficient' => $this->faker->randomFloat(2, 0, 1),
        ];
    }
}
