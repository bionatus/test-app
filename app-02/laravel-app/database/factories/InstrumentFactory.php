<?php

namespace Database\Factories;

use App\Models\Instrument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Instrument create($attributes = [], ?Model $parent = null)
 * @method Collection|Instrument make($attributes = [], ?Model $parent = null)
 */
class InstrumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fn(array $attributes) => Str::slug($attributes['name']),
            'name' => $this->faker->sentence(3),
        ];
    }

    public function name(string $name): self
    {
        return $this->state(function() use ($name) {
            return [
                'slug' => Str::slug($name),
                'name' => $name,
            ];
        });
    }
}
