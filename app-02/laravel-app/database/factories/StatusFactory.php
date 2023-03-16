<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Status create($attributes = [], ?Model $parent = null)
 * @method Collection|Status make($attributes = [], ?Model $parent = null)
 */
class StatusFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name,
            'slug' => function(array $attributes) {
                return Str::slug($attributes['name']);
            },
        ];
    }
}
