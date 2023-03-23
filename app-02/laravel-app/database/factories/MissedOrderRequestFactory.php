<?php

namespace Database\Factories;

use App\Models\MissedOrderRequest;
use App\Models\Model;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|MissedOrderRequest create($attributes = [], ?Model $parent = null)
 * @method Collection|MissedOrderRequest make($attributes = [], ?Model $parent = null)
 */
class MissedOrderRequestFactory extends Factory
{
    public function definition(): array
    {

        return [
            'order_id'           => Order::factory(),
            'missed_supplier_id' => Supplier::factory(),
        ];
    }

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'order_id' => $order,
            ];
        });
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'missed_supplier_id' => $supplier,
            ];
        });
    }
}
