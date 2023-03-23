<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CartItem create($attributes = [], ?Model $parent = null)
 * @method Collection|CartItem make($attributes = [], ?Model $parent = null)
 */
class CartItemFactory extends Factory
{
    public function definition()
    {
        return [
            'uuid'     => $this->faker->unique()->uuid,
            'item_id'  => Item::factory(),
            'cart_id'  => Cart::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
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

    public function usingCart(Cart $cart): self
    {
        return $this->state(function() use ($cart) {
            return [
                'cart_id' => $cart,
            ];
        });
    }
}
