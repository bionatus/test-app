<?php

namespace Database\Factories;

use App\Models\CartSupplyCounter;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CartSupplyCounter create($attributes = [], ?Model $parent = null)
 * @method Collection|CartSupplyCounter make($attributes = [], ?Model $parent = null)
 */
class CartSupplyCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'supply_id' => Supply::factory(),
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

    public function usingSupply(Supply $supply): self
    {
        return $this->state(function() use ($supply) {
            return [
                'supply_id' => $supply,
            ];
        });
    }
}
