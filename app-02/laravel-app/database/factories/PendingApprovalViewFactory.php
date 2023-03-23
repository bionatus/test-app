<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PendingApprovalView;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PendingApprovalView create($attributes = [], ?Model $parent = null)
 * @method Collection|PendingApprovalView make($attributes = [], ?Model $parent = null)
 */
class PendingApprovalViewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id'  => User::factory(),
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
}
