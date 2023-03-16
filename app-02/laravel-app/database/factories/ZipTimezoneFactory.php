<?php

namespace Database\Factories;

use App\Constants\Timezones;
use App\Models\ZipTimezone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ZipTimezone create($attributes = [], ?Model $parent = null)
 * @method Collection|ZipTimezone make($attributes = [], ?Model $parent = null)
 */
class ZipTimezoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country'  => $this->faker->countryCode,
            'state'    => fn(array $attributes) => "{$attributes['country']}-{$this->faker->stateAbbr}",
            'county'   => $this->faker->state,
            'city'     => $this->faker->city,
            'zip'      => $this->faker->postcode,
            'timezone' => $this->faker->randomElement(Timezones::ALLOWED_TIMEZONES),
        ];
    }
}
