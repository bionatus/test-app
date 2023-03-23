<?php

namespace Database\Factories;

use App\Models\AppVersion;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @method Collection|AppVersion create($attributes = [], ?Model $parent = null)
 * @method Collection|AppVersion make($attributes = [], ?Model $parent = null)
 */
class AppVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'min'       => $this->faker->randomDigit . '.' . $this->faker->randomDigit . '.' . $this->faker->randomDigit,
            'current'   => $this->faker->randomDigit . '.' . $this->faker->randomDigit . '.' . $this->faker->randomDigit,
            'video_url' => $this->faker->url,
            'message'   => $this->faker->randomHtml(),
        ];
    }
}
