<?php

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Tool create($attributes = [], ?Model $parent = null)
 * @method Collection|Tool make($attributes = [], ?Model $parent = null)
 */
class ToolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fn(array $attributes) => Str::slug($attributes['name']),
            'name' => $this->faker->unique()->name,
        ];
    }
}
