<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupplierUser create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierUser createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierUser make($attributes = [], ?Model $parent = null)
 */
class SupplierUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id'     => Supplier::factory(),
            'user_id'         => User::factory(),
            'status'          => SupplierUser::STATUS_UNCONFIRMED,
            'cash_buyer'      => false,
            'visible_by_user' => true,
        ];
    }

    public function usingSupplier(Supplier $store): self
    {
        return $this->state(function() use ($store) {
            return [
                'supplier_id' => $store,
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

    public function unconfirmed(): self
    {
        return $this->state(function() {
            return [
                'status' => SupplierUser::STATUS_UNCONFIRMED,
            ];
        });
    }

    public function confirmed(): self
    {
        return $this->state(function() {
            return [
                'status' => SupplierUser::STATUS_CONFIRMED,
            ];
        });
    }

    public function removed(): self
    {
        return $this->state(function() {
            return [
                'status' => SupplierUser::STATUS_REMOVED,
            ];
        });
    }

    public function notVisible(): self
    {
        return $this->state(function() {
            return [
                'visible_by_user' => false,
            ];
        });
    }
}
