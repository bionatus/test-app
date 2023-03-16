<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SettingSupplier create($attributes = [], ?Model $parent = null)
 * @method Collection|SettingSupplier make($attributes = [], ?Model $parent = null)
 */
class SettingStaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'staff_id'   => Staff::factory(),
            'setting_id' => Setting::factory(),
            'value'      => true,
        ];
    }

    public function usingStaff(Staff $staff): self
    {
        return $this->state(function() use ($staff) {
            return [
                'staff_id' => $staff,
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
