<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\ItemOrderSnap;
use App\Models\OrderSnap;
use App\Models\Replacement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ItemOrderSnap create($attributes = [], ?Model $parent = null)
 * @method Collection|ItemOrderSnap createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|ItemOrderSnap make($attributes = [], ?Model $parent = null)
 */
class ItemOrderSnapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id'       => Item::factory(),
            'order_snap_id' => OrderSnap::factory(),
            'quantity'      => $this->faker->numberBetween(1, 10),
            'status'        => ItemOrder::STATUS_PENDING,
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

    public function usingOrderSnap(OrderSnap $orderSnap): self
    {
        return $this->state(function() use ($orderSnap) {
            return [
                'order_snap_id' => $orderSnap,
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
}
