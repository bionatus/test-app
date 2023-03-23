<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\OrderDelivery;
use App\Models\ShipmentDelivery;
use App\Models\ShipmentDeliveryPreference;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ShipmentDelivery create($attributes = [], ?Model $parent = null)
 * @method Collection|ShipmentDelivery createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|ShipmentDelivery make($attributes = [], ?Model $parent = null)
 */
class ShipmentDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                              => OrderDelivery::factory()->shipmentDelivery(),
            'destination_address_id'          => Address::factory(),
            'shipment_delivery_preference_id' => ShipmentDeliveryPreference::factory(),
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

    public function usingShipmentDeliveryPreference(ShipmentDeliveryPreference $preference): self
    {
        return $this->state(function() use ($preference) {
            return [
                'shipment_delivery_preference_id' => $preference,
            ];
        });
    }
}
