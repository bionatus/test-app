<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ItemWishlist create($attributes = [], ?Model $parent = null)
 * @method Collection|ItemWishlist createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|ItemWishlist make($attributes = [], ?Model $parent = null)
 */
class ItemWishlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'        => $this->faker->unique()->uuid,
            'quantity'    => $this->faker->numberBetween(0, 100),
            'item_id'     => Item::factory(),
            'wishlist_id' => Wishlist::factory(),
        ];
    }

    public function usingWishlist(Wishlist $wishlist): self
    {
        return $this->state(function() use ($wishlist) {
            return [
                'wishlist_id' => $wishlist,
            ];
        });
    }

    public function usingItem(Item $item): self
    {
        return $this->state(function() use ($item) {
            return [
                'item_id' => $item,
            ];
        });
    }
}
