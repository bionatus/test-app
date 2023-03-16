<?php

namespace Database\Factories;

use App\Models\ApiUsage;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|ApiUsage create($attributes = [], ?Model $parent = null)
 * @method Collection|ApiUsage make($attributes = [], ?Model $parent = null)
 */
class ApiUsageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => Carbon::now()->format('Y-m-d'),
        ];
    }

    public function withUser(): self
    {
        return $this->state(function() {
            return [
                'user_id'   => User::factory(),
            ];
        });
    }

    public function withSupplier(): self
    {
        return $this->state(function() {
            return [
                'supplier_id'   => Supplier::factory(),
            ];
        });
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id'   => $user,
            ];
        });
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier){
            return [
                'supplier_id'   => $supplier,
            ];
        });
    }
}
