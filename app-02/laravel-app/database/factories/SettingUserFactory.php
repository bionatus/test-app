<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SettingUser create($attributes = [], ?Model $parent = null)
 * @method Collection|SettingUser make($attributes = [], ?Model $parent = null)
 */
class SettingUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'setting_id' => Setting::factory(),
            'value'      => 'value',
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

    public function usingSetting(Setting $setting): self
    {
        return $this->state(function() use ($setting) {
            return [
                'setting_id' => $setting,
            ];
        });
    }
}
