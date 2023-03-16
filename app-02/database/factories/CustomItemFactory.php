<?php

namespace Database\Factories;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @method Collection|CustomItem create($attributes = [], ?Model $parent = null)
 * @method Collection|CustomItem make($attributes = [], ?Model $parent = null)
 */
class CustomItemFactory extends Factory
{
    public function definition()
    {
        return [
            'id'          => Item::factory()->customItem(),
            'name'        => $this->faker->unique()->word,
            'creator_type' => function() {
                return Relation::getAliasByModel(User::class);
            },
            'creator_id'   => function() {
                return User::factory();
            },
        ];
    }

    public function usingItem(Item $item): self
    {
        return $this->state(function() use ($item) {
            return [
                'id' => $item,
            ];
        });
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'creator_id'   => $supplier,
                'creator_type' => Relation::getAliasByModel(get_class($supplier)),
            ];
        });
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'creator_id'   => $user,
                'creator_type' => Relation::getAliasByModel(get_class($user)),
            ];
        });
    }
}
