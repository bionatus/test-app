<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Item create($attributes = [], ?Model $parent = null)
 * @method Collection|Item make($attributes = [], ?Model $parent = null)
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->unique()->uuid,
            'type' => Item::TYPE_SUPPLY,
        ];
    }

    public function part(): self
    {
        return $this->state(function() {
            return [
                'type' => Item::TYPE_PART,
            ];
        });
    }

    public function supply(): self
    {
        return $this->state(function() {
            return [
                'type' => Item::TYPE_SUPPLY,
            ];
        });
    }

    public function customItem(): self
    {
        return $this->state(function() {
            return [
                'type' => Item::TYPE_CUSTOM_ITEM,
            ];
        });
    }
}
