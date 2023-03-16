<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Str;

/**
 * @method Collection|Brand create($attributes = [], ?Model $parent = null)
 * @method Collection|Brand make($attributes = [], ?Model $parent = null)
 */
class BrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'slug' => function(array $attributes) {
                return Str::slug($attributes['name']);
            },
            'logo' => [],
        ];
    }

    public function logo(): self
    {
        return $this->state(function() {
            $brandLogo = [
                [
                    "id"         => $this->faker->lexify('????????????'),
                    "url"        => $this->faker->imageUrl(),
                    "size"       => $this->faker->randomNumber(4),
                    "type"       => "image/png",
                    "filename"   => "file.png",
                    'thumbnails' => [
                        "full"  => [
                            "url"    => $this->faker->imageUrl(120, 60),
                            "width"  => 120,
                            "height" => 60,
                        ],
                        "large" => [
                            "url"    => $this->faker->imageUrl(120, 60),
                            "width"  => 120,
                            "height" => 60,
                        ],
                        "small" => [
                            "url"    => $this->faker->imageUrl(120, 60),
                            "width"  => 72,
                            "height" => 36,
                        ],
                    ],
                ],
            ];

            return [
                'logo' => $brandLogo,
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
}
