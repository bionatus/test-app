<?php

namespace Database\Factories;

use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CartOrderItem create($attributes = [], ?Model $parent = null)
 * @method Collection|CartOrderItem make($attributes = [], ?Model $parent = null)
 */
class CartOrderItemFactory extends Factory
{
    public function definition()
    {
        return [
            'item_id'       => Item::factory(),
            'cart_order_id' => CartOrder::factory(),
            'quantity'      => $this->faker->numberBetween(1, 10),
        ];
    }

    public function usingItem(Item $item): self
    {
        return $this->state(function() use ($item) {
            return [
                'item_id' => $item,
            ];
        });
    }

    public function usingCartOrder(CartOrder $cartOrder): self
    {
        return $this->state(function() use ($cartOrder) {
            return [
                'cart_order_id' => $cartOrder,
            ];
        });
    }
}
