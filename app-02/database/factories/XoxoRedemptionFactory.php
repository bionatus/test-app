<?php

namespace Database\Factories;

use App\Models\XoxoRedemption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|XoxoRedemption create($attributes = [], ?Model $parent = null)
 * @method Collection|XoxoRedemption make($attributes = [], ?Model $parent = null)
 */
class XoxoRedemptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'               => $this->faker->unique()->uuid,
            'redemption_code'    => $this->faker->numberBetween(),
            'voucher_code'       => $this->faker->numberBetween(),
            'name'               => $this->faker->sentence(),
            'image'              => $this->faker->imageUrl(),
            'value_denomination' => $valueDenomination = $this->faker->numberBetween(0, 9999),
            'amount_charged'     => $valueDenomination,
            'created_at'         => $this->faker->dateTime,
        ];
    }
}
