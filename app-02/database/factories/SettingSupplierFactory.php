<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SettingSupplier create($attributes = [], ?Model $parent = null)
 * @method Collection|SettingSupplier make($attributes = [], ?Model $parent = null)
 */
class SettingSupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'setting_id'  => Setting::factory(),
            'value'       => true,
        ];
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }

    public function usingSetting(Setting $setting): self
    {
        return $this->state(function() use ($setting) {
            return [
                'setting_id' => $setting,
            ];
        });
    }
}
