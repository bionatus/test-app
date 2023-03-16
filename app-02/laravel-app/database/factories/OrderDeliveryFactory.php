<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderDelivery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OrderDelivery create($attributes = [], ?Model $parent = null)
 * @method Collection|OrderDelivery createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OrderDelivery make($attributes = [], ?Model $parent = null)
 */
class OrderDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'             => Order::factory(),
            'type'                 => OrderDelivery::TYPE_PICKUP,
            'requested_date'       => Carbon::now(),
            'requested_start_time' => '09:00',
            'requested_end_time'   => '12:00',
        ];
    }

    public function curriDelivery(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderDelivery::TYPE_CURRI_DELIVERY,
            ];
        });
    }

    public function warehouseDelivery(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderDelivery::TYPE_WAREHOUSE_DELIVERY,
            ];
        });
    }

    public function otherDelivery(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderDelivery::TYPE_OTHER_DELIVERY,
            ];
        });
    }

    public function pickup(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderDelivery::TYPE_PICKUP,
            ];
        });
    }

    public function shipmentDelivery(): self
    {
        return $this->state(function() {
            return [
                'type' => OrderDelivery::TYPE_SHIPMENT_DELIVERY,
            ];
        });
    }

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'order_id' => $order,
            ];
        });
    }
}
