<?php

namespace Database\Factories;

use App\Models\PlainTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|PlainTag create($attributes = [], ?Model $parent = null)
 * @method Collection|PlainTag make($attributes = [], ?Model $parent = null)
 */
class PlainTagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fn(array $attributes) => Str::slug($attributes['name']),
            'name' => $this->faker->unique()->name,
            'type' => PlainTag::TYPE_GENERAL,
        ];
    }

    public function general(): self
    {
        return $this->state(function() {
            return [
                'type' => PlainTag::TYPE_GENERAL,
            ];
        });
    }

    public function issue(): self
    {
        return $this->state(function() {
            return [
                'type' => PlainTag::TYPE_ISSUE,
            ];
        });
    }

    public function more(): self
    {
        return $this->state(function() {
            return [
                'type' => PlainTag::TYPE_MORE,
            ];
        });
    }
}
