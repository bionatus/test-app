<?php

namespace Database\Factories;

use App\Models\Tip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Tip create($attributes = [], ?Model $parent = null)
 * @method Collection|Tip make($attributes = [], ?Model $parent = null)
 */
class TipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => $this->faker->text,
            'type'        => $this->faker->text,
        ];
    }
}
