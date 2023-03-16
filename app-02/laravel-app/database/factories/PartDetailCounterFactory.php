<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PartDetailCounter create($attributes = [], ?Model $parent = null)
 * @method Collection|PartDetailCounter createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|PartDetailCounter make($attributes = [], ?Model $parent = null)
 */
class PartDetailCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'part_id' => Part::factory(),
        ];
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'part_id' => $part,
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

    public function usingPartSearchCounter(PartSearchCounter $partSearchCounter): self
    {
        return $this->state(function() use($partSearchCounter){
            return [
                'part_search_counter_id' => $partSearchCounter,
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

    public function withPartSearchCounter(): self
    {
        return $this->state(function() {
            return [
                'part_search_counter_id' => PartSearchCounter::factory(),
            ];
        });
    }
}
