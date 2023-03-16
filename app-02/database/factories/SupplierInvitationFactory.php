<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupplierInvitation create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierInvitation createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierInvitation make($attributes = [], ?Model $parent = null)
 */
class SupplierInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'user_id'     => User::factory(),
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
