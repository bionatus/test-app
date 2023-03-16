<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|BrandDetailCounter create($attributes = [], ?Model $parent = null)
 * @method Collection|BrandDetailCounter make($attributes = [], ?Model $parent = null)
 */
class BrandDetailCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
        ];
    }

    public function usingBrand(Brand $brand): self
    {
        return $this->state(function() use ($brand) {
            return [
                'brand_id' => $brand,
            ];
        });
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingStaff(Staff $staff): self
    {
        return $this->state(function() use ($staff) {
            return [
                'staff_id' => $staff,
            ];
        });
    }

    public function withStaff(): self
    {
        return $this->state(function() {
            return [
                'staff_id' => Staff::factory(),
            ];
        });
    }

    public function withUser(): self
    {
        return $this->state(function() {
            return [
                'user_id' => User::factory(),
            ];
        });
    }
}
