<?php

namespace Database\Factories;

use App\Models\CartOrder;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CartOrder create($attributes = [], ?Model $parent = null)
 * @method Collection|CartOrder make($attributes = [], ?Model $parent = null)
 */
class CartOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
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
}
