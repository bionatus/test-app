<?php

namespace Database\Factories;

use App\Models\SupplyCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Str;

/**
 * @method Collection|SupplyCategory create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplyCategory make($attributes = [], ?Model $parent = null)
 */
class SupplyCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->sentence();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
        ];
    }

    public function usingParent(SupplyCategory $supplyCategory): self
    {
        return $this->state(function() use ($supplyCategory) {
            return [
                'parent_id' => $supplyCategory,
            ];
        });
    }

    public function name(string $name): self
    {
        return $this->state(function() use ($name) {
            return [
                'slug' => Str::slug($name),
                'name' => $name,
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
}
