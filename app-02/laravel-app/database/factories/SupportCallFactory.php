<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupportCall create($attributes = [], ?Model $parent = null)
 * @method Collection|SupportCall make($attributes = [], ?Model $parent = null)
 */
class SupportCallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category' => $this->faker->slug,
            'user_id'  => User::factory(),
        ];
    }

    public function oem(): self
    {
        return $this->state(function() {
            return [
                'category' => SupportCall::CATEGORY_OEM,
                'oem_id'   => Oem::factory(),
            ];
        });
    }

    public function missingOemBrand(): self
    {
        return $this->state(function() {
            return [
                'category'                 => SupportCall::CATEGORY_MISSING_OEM,
                'missing_oem_brand_id'     => Brand::factory(),
                'missing_oem_model_number' => 'fake model number',
            ];
        });
    }

    public function usingOem(Oem $oem): self
    {
        return $this->state(function() use ($oem) {
            return [
                'category' => SupportCall::CATEGORY_OEM,
                'oem_id'   => $oem,
            ];
        });
    }

    public function usingMissingOemBrand(Brand $missingOemBrand): self
    {
        return $this->state(function() use ($missingOemBrand) {
            return [
                'category'                 => SupportCall::CATEGORY_MISSING_OEM,
                'missing_oem_brand_id'     => $missingOemBrand,
                'missing_oem_model_number' => 'fake model number',
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
}
