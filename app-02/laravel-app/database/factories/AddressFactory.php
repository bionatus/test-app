<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Address create($attributes = [], ?Model $parent = null)
 * @method Collection|Address make($attributes = [], ?Model $parent = null)
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'address_1' => $this->faker->streetAddress,
            'city'      => $this->faker->city,
            'state'     => $this->faker->state,
            'country'   => $this->faker->country,
            'zip_code'  => $this->faker->postcode,
        ];
    }
}
