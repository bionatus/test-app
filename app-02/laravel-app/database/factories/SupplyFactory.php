<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|Supply create($attributes = [], ?Model $parent = null)
 * @method Collection|Supply make($attributes = [], ?Model $parent = null)
 */
class SupplyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                 => Item::factory()->supply(),
            'name'               => $this->faker->unique()->word,
            'internal_name'      => $this->faker->unique()->word,
            'supply_category_id' => SupplyCategory::factory(),
        ];
    }

    public function usingSupplyCategory(SupplyCategory $supplyCategory): self
    {
        return $this->state(function() use ($supplyCategory) {
            return [
                'supply_category_id' => $supplyCategory,
            ];
        });
    }

    public function name(string $name): self
    {
        return $this->state(function() use ($name) {
            return [
                'name' => $name,
            ];
        });
    }

    public function internalName(string $internalName): self
    {
        return $this->state(function() use ($internalName) {
            return [
                'internal_name' => $internalName,
            ];
        });
    }

    public function sort(int $sort = null): self
    {
        $sort = $sort ?: $this->faker->randomDigit;

        return $this->state(function() use ($sort) {
            return [
                'sort' => $sort,
            ];
        });
    }

    public function visible(): self
    {
        return $this->state(function() {
            return [
                'visible_at' => Carbon::now(),
            ];
        });
    }

    public function nonVisible(): self
    {
        return $this->state(function() {
            return [
                'visible_at' => null,
            ];
        });
    }
}
