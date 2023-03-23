<?php

namespace Database\Factories;

use App\Models\PartBrand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Str;

/**
 * @method Collection|PartBrand create($attributes = [], ?Model $parent = null)
 * @method Collection|PartBrand make($attributes = [], ?Model $parent = null)
 */
class PartBrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'      => $this->faker->unique()->name,
            'slug'      => function(array $attributes) {
                return Str::slug($attributes['name']);
            },
            'logo'      => $this->faker->imageUrl(),
            'preferred' => false,
        ];
    }

    public function published(): self
    {
        return $this->state(function() {
            return [
                'published_at' => Carbon::now(),
            ];
        });
    }

    public function unpublished(): self
    {
        return $this->state(function() {
            return [
                'published_at' => null,
            ];
        });
    }

    public function preferred(): self
    {
        return $this->state(function() {
            return [
                'preferred' => true,
            ];
        });
    }

    public function unPreferred(): self
    {
        return $this->state(function() {
            return [
                'preferred' => false,
            ];
        });
    }
}
