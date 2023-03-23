<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Product create($attributes = [], ?Model $parent = null)
 * @method Collection|Product make($attributes = [], ?Model $parent = null)
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'        => $this->faker->unique()->uuid,
            'model'     => $this->faker->unique()->word,
            'brand'     => $this->faker->unique()->company,
            'fields'    => [],
            'series_id' => Series::factory(),
        ];
    }

    public function usingSeries(Series $series): self
    {
        return $this->state(function() use ($series) {
            return [
                'series_id' => $series,
            ];
        });
    }
}
