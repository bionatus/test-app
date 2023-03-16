<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\OtherDelivery;
use App\Models\OrderDelivery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OtherDelivery create($attributes = [], ?Model $parent = null)
 * @method Collection|OtherDelivery createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OtherDelivery make($attributes = [], ?Model $parent = null)
 */
class OtherDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                     => OrderDelivery::factory()->otherDelivery(),
            'destination_address_id' => Address::factory(),
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

    public function usingDestinationAddress(Address $address): self
    {
        return $this->state(function() use ($address) {
            return [
                'destination_address_id' => $address,
            ];
        });
    }
}
