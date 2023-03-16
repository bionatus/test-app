<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|Series create($attributes = [], ?Model $parent = null)
 * @method Collection|Series make($attributes = [], ?Model $parent = null)
 */
class SeriesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'uuid'     => $this->faker->unique()->uuid,
            'name'     => $this->faker->name,
            'image'    => $this->faker->imageUrl(),
        ];
    }

    public function usingBrand(Brand $brand): self
    {
        return $this->state(function() use ($brand) {
            return [
                'brand_id' => $brand,
            ];
        });
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

    public function active(): self
    {
        return $this->published()->state(function() {
            return [
                'brand_id' => Brand::factory()->published(),
            ];
        });
    }
}
