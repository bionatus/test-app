<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OrderSubstatus create($attributes = [], ?Model $parent = null)
 * @method Collection|OrderSubstatus make($attributes = [], ?Model $parent = null)
 */
class OrderSubstatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'     => Order::factory(),
            'substatus_id' => Substatus::factory(),
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

    public function usingSubstatus(Substatus $substatus): self
    {
        return $this->state(function() use ($substatus) {
            return [
                'substatus_id' => $substatus,
            ];
        });
    }

    public function usingSubstatusId(int $substatusId): self
    {
        return $this->state(function() use ($substatusId) {
            return [
                'substatus_id' => $substatusId,
            ];
        });
    }
}
