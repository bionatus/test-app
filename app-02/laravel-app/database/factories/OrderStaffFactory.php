<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStaff;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OrderStaff create($attributes = [], ?Model $parent = null)
 * @method Collection|OrderStaff createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OrderStaff make($attributes = [], ?Model $parent = null)
 */
class OrderStaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'staff_id' => Staff::factory(),
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

    public function usingStaff(Staff $staff): self
    {
        return $this->state(function() use ($staff) {
            return [
                'staff_id' => $staff,
            ];
        });
    }
}
