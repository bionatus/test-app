<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\OrderDelivery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CurriDelivery create($attributes = [], ?Model $parent = null)
 * @method Collection|CurriDelivery createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|CurriDelivery make($attributes = [], ?Model $parent = null)
 */
class CurriDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                     => OrderDelivery::factory()->curriDelivery(),
            'origin_address_id'      => Address::factory(),
            'destination_address_id' => Address::factory(),
            'vehicle_type'           => CurriDelivery::VEHICLE_TYPE_CAR,
        ];
    }

    public function car(): self
    {
        return $this->state(function() {
            return [
                'vehicle_type' => CurriDelivery::VEHICLE_TYPE_CAR,
            ];
        });
    }

    public function rackTruck(): self
    {
        return $this->state(function() {
            return [
                'vehicle_type' => CurriDelivery::VEHICLE_TYPE_RACK_TRUCK,
            ];
        });
    }

    public function confirmedBySupplier(): self
    {
        return $this->state(function() {
            return [
                'supplier_confirmed_at' => Carbon::now(),
            ];
        });
    }

    public function confirmedByUser(): self
    {
        return $this->state(function() {
            return [
                'user_confirmed_at' => Carbon::now(),
            ];
        });
    }

    public function usingOrderDelivery(OrderDelivery $orderDelivery): self
    {
        return $this->state(function() use ($orderDelivery) {
            return [
                'id' => $orderDelivery,
            ];
        });
    }

    public function usingOriginAddress(Address $address): self
    {
        return $this->state(function() use ($address) {
            return [
                'origin_address_id' => $address,
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
