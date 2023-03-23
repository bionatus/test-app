<?php

namespace Database\Factories;

use App\Constants\Timezones;
use App\Models\StateTimezone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|StateTimezone create($attributes = [], ?Model $parent = null)
 * @method Collection|StateTimezone make($attributes = [], ?Model $parent = null)
 */
class StateTimezoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country'  => $this->faker->countryCode,
            'state'    => fn(array $attributes) => "{$attributes['country']}-{$this->faker->stateAbbr}",
            'timezone' => $this->faker->randomElement(Timezones::ALLOWED_TIMEZONES),
        ];
    }
}
