<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\SharedOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SharedOrder create($attributes = [], ?Model $parent = null)
 * @method Collection|SharedOrder make($attributes = [], ?Model $parent = null)
 */
class SharedOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'order_id' => Order::factory(),
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

    public function usingOrder(Order $order): self
    {
        return $this->state(function() use ($order) {
            return [
                'order_id' => $order,
            ];
        });
    }
}
