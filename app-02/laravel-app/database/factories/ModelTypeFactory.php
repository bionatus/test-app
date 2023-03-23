<?php

namespace Database\Factories;

use App\Models\ModelType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|ModelType create($attributes = [], ?Model $parent = null)
 * @method Collection|ModelType make($attributes = [], ?Model $parent = null)
 */
class ModelTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'slug' => function(array $attributes) {
                return Str::slug($attributes['name']);
            },
        ];
    }
}
