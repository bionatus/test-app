<?php

namespace Database\Factories;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|AppSetting create($attributes = [], ?Model $parent = null)
 * @method Collection|AppSetting make($attributes = [], ?Model $parent = null)
 */
class AppSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label' => $this->faker->unique()->text(255),
            'slug'  => fn(array $attributes) => Str::slug($attributes['label']),
            'value' => $this->faker->text(255),
            'type'  => AppSetting::TYPE_STRING,
        ];
    }
}
