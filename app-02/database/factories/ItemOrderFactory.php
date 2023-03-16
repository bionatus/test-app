<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Replacement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ItemOrder create($attributes = [], ?Model $parent = null)
 * @method Collection|ItemOrder createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|ItemOrder make($attributes = [], ?Model $parent = null)
 */
class ItemOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'               => $this->faker->unique()->uuid,
            'item_id'            => Item::factory(),
            'order_id'           => Order::factory(),
            'quantity'           => $quantity = $this->faker->numberBetween(1, 10),
            'quantity_requested' => $quantity,
            'status'             => ItemOrder::STATUS_PENDING,
            'initial_request'    => true,
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

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'order_id' => $order,
            ];
        });
    }

    public function usingReplacement(Replacement $replacement): self
    {
        return $this->state(function() use ($replacement) {
            return [
                'replacement_id' => $replacement,
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function() {
            return [
                'status' => ItemOrder::STATUS_PENDING,
            ];
        });
    }

    public function available(): self
    {
        return $this->state(function() {
            return [
                'status' => ItemOrder::STATUS_AVAILABLE,
            ];
        });
    }

    public function notAvailable(): self
    {
        return $this->state(function() {
            return [
                'status' => ItemOrder::STATUS_NOT_AVAILABLE,
            ];
        });
    }

    public function seeBelowItem(): self
    {
        return $this->state(function() {
            return [
                'status' => ItemOrder::STATUS_SEE_BELOW_ITEM,
            ];
        });
    }

    public function removed(): self
    {
        return $this->state(function() {
            return [
                'status' => ItemOrder::STATUS_REMOVED,
            ];
        });
    }

    public function notInitialRequest(): self
    {
        return $this->state(function() {
            return [
                'initial_request' => false,
            ];
        });
    }
}
