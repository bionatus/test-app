<?php

namespace Database\Factories;

use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OemDetailCounter create($attributes = [], ?Model $parent = null)
 * @method Collection|OemDetailCounter createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OemDetailCounter make($attributes = [], ?Model $parent = null)
 */
class OemDetailCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'oem_id' => Oem::factory(),
        ];
    }

    public function usingOem(Oem $oem): self
    {
        return $this->state(function() use ($oem) {
            return [
                'oem_id' => $oem,
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

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingOemSearchCounter(OemSearchCounter $oemSearchCounter): self
    {
        return $this->state(function() use ($oemSearchCounter){
            return [
                'oem_search_counter_id' => $oemSearchCounter,
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

    public function withOemSearchCounter(): self
    {
        return $this->state(function() {
            return [
                'oem_search_counter_id' => OemSearchCounter::factory(),
            ];
        });
    }
}
