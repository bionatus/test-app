<?php

namespace Database\Factories;

use App\Models\ShipmentDeliveryPreference;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ShipmentDeliveryPreference create($attributes = [], ?Model $parent = null)
 * @method Collection|ShipmentDeliveryPreference createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|ShipmentDeliveryPreference make($attributes = [], ?Model $parent = null)
 */
class ShipmentDeliveryPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => $this->faker->slug,
        ];
    }
}
