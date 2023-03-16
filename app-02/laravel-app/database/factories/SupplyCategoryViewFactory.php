<?php

namespace Database\Factories;

use App\Models\SupplyCategory;
use App\Models\SupplyCategoryView;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupplyCategoryView create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplyCategoryView make($attributes = [], ?Model $parent = null)
 */
class SupplyCategoryViewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'supply_category_id' => SupplyCategory::factory(),
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingSupplyCategory(SupplyCategory $supplyCategory): self
    {
        return $this->state(function() use ($supplyCategory) {
            return [
                'supply_category_id' => $supplyCategory,
            ];
        });
    }
}
