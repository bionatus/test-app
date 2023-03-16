<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\BrandSupplier;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|BrandSupplier create($attributes = [], ?Model $parent = null)
 * @method Collection|BrandSupplier make($attributes = [], ?Model $parent = null)
 */
class BrandSupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'brand_id'    => Brand::factory(),
            'supplier_id' => Supplier::factory(),
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

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }
}
