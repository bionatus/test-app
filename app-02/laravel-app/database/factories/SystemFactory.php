<?php

namespace Database\Factories;

use App\Models\System;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|System create($attributes = [], ?Model $parent = null)
 * @method Collection|System make($attributes = [], ?Model $parent = null)
 */
class SystemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fn(array $attributes) => Str::slug($attributes['name']),
            'name' => $this->faker->name,
        ];
    }
}