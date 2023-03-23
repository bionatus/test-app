<?php

namespace Database\Factories;

use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\Status;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OrderSnap create($attributes = [], ?Model $parent = null)
 * @method Collection|OrderSnap createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OrderSnap make($attributes = [], ?Model $parent = null)
 */
class OrderSnapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status'   => Status::STATUS_PENDING,
        ];
    }

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'order_id' => $order,
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

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }

    public function usingOem(Oem $oem): self
    {
        return $this->state(function() use ($oem) {
            return [
                'oem_id' => $oem,
            ];
        });
    }
}
