<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderLockedData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OrderLockedData create($attributes = [], ?Model $parent = null)
 * @method Collection|OrderLockedData createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OrderLockedData make($attributes = [], ?Model $parent = null)
 */
class OrderLockedDataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'        => Order::factory(),
            'user_first_name' => $this->faker->firstName,
            'user_last_name'  => $this->faker->lastName,
            'channel'         => $this->faker->uuid,
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
}
