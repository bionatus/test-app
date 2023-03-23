<?php

namespace Database\Factories;

use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PubnubChannel create($attributes = [], ?Model $parent = null)
 * @method Collection|PubnubChannel make($attributes = [], ?Model $parent = null)
 */
class PubnubChannelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'supplier_id' => Supplier::factory(),
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

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }
}
