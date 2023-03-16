<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Cart create($attributes = [], ?Model $parent = null)
 * @method Collection|Cart make($attributes = [], ?Model $parent = null)
 */
class CartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
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

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }
}
