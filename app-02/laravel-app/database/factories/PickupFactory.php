<?php

namespace Database\Factories;

use App\Models\OrderDelivery;
use App\Models\Pickup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Pickup create($attributes = [], ?Model $parent = null)
 * @method Collection|Pickup createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Pickup make($attributes = [], ?Model $parent = null)
 */
class PickupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => OrderDelivery::factory()->pickup(),
        ];
    }

    public function usingOrderDelivery(OrderDelivery $orderDelivery): self
    {
        return $this->state(function() use ($orderDelivery) {
            return [
                'id' => $orderDelivery,
            ];
        });
    }
}
