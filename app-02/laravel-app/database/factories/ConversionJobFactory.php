<?php

namespace Database\Factories;

use App\Models\ConversionJob;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ConversionJob create($attributes = [], ?Model $parent = null)
 * @method Collection|ConversionJob make($attributes = [], ?Model $parent = null)
 */
class ConversionJobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'control'  => $this->faker->unique()->word,
            'standard' => $this->faker->text(100),
            'optional' => $this->faker->text(100),
            'image'    => $this->faker->word . '.png',
            'retrofit' => $this->faker->text(100),
        ];
    }
}
