<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\WarehouseDelivery;
use App\Models\OrderDelivery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|WarehouseDelivery create($attributes = [], ?Model $parent = null)
 * @method Collection|WarehouseDelivery createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|WarehouseDelivery make($attributes = [], ?Model $parent = null)
 */
class WarehouseDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                     => OrderDelivery::factory()->warehouseDelivery(),
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
