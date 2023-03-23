<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Setting create($attributes = [], ?Model $parent = null)
 * @method Collection|Setting make($attributes = [], ?Model $parent = null)
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->sentence;

        return [
            'name'          => $name,
            'slug'          => Str::slug($name),
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => $this->faker->randomElement([true, false]),
        ];
    }

    public function boolean(): self
    {
        return $this->state(function() {
            return [
                'type'  => Setting::TYPE_BOOLEAN,
                'value' => $this->faker->randomElement([true, false]),
            ];
        });
    }

    public function integer(): self
    {
        return $this->state(function() {
            return [
                'type'  => Setting::TYPE_INTEGER,
                'value' => rand(0, 1000),
            ];
        });
    }

    public function double(): self
    {
        return $this->state(function() {
            return [
                'type'  => Setting::TYPE_DOUBLE,
                'value' => rand(0, 1000) / 100,
            ];
        });
    }

    public function string(): self
    {
        return $this->state(function() {
            return [
                'type'  => Setting::TYPE_STRING,
                'value' => $this->faker->word,
            ];
        });
    }

    public function groupNotification(): self
    {
        return $this->state(function() {
            return [
                'group' => Setting::GROUP_NOTIFICATION,
            ];
        });
    }

    public function groupAgent(): self
    {
        return $this->state(function() {
            return [
                'group' => Setting::GROUP_AGENT,
            ];
        });
    }

    public function groupValidation(): self
    {
        return $this->state(function() {
            return [
                'group' => Setting::GROUP_VALIDATION,
            ];
        });
    }

    public function agentAvailable(): self
    {
        return $this->state(function() {
            return [
                'slug'          => Setting::SLUG_AGENT_AVAILABLE,
                'group'         => Setting::GROUP_AGENT,
                'applicable_to' => User::MORPH_ALIAS,
                'type'          => Setting::TYPE_BOOLEAN,
                'value'         => false,
            ];
        });
    }

    public function applicableToUser(): self
    {
        return $this->state(function() {
            return [
                'applicable_to' => User::MORPH_ALIAS,
            ];
        });
    }

    public function applicableToSupplier(): self
    {
        return $this->state(function() {
            return [
                'applicable_to' => Supplier::MORPH_ALIAS,
            ];
        });
    }

    public function applicableToStaff(): self
    {
        return $this->state(function() {
            return [
                'applicable_to' => Staff::MORPH_ALIAS,
            ];
        });
    }
}
