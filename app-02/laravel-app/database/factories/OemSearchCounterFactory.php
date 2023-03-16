<?php

namespace Database\Factories;

use App\Models\OemSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OemSearchCounter create($attributes = [], ?Model $parent = null)
 * @method Collection|OemSearchCounter createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OemSearchCounter make($attributes = [], ?Model $parent = null)
 */
class OemSearchCounterFactory extends Factory
{
    public function definition()
    {
        return [
            'uuid'     => $this->faker->unique()->uuid,
            'criteria' => $this->faker->text(255),
            'results'  => $this->faker->numberBetween(0, 999),
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

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
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
