<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Point;
use App\Models\User;
use App\Models\XoxoRedemption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @method Collection|Point create($attributes = [], ?Model $parent = null)
 * @method Collection|Point createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Point make($attributes = [], ?Model $parent = null)
 */
class PointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'object_type'   => function() {
                return Relation::getAliasByModel(Order::class);
            },
            'object_id'     => function() {
                return Order::factory();
            },
            'action'        => Point::ACTION_ORDER_APPROVED,
            'coefficient'   => $this->faker->randomFloat(2, 0, 1),
            'multiplier'    => $this->faker->numberBetween(1, 5),
            'points_earned' => $this->faker->numberBetween(1, 100),
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
                'object_id'   => $order,
                'object_type' => Relation::getAliasByModel(get_class($order)),
            ];
        });
    }

    public function usingXoxoRedemption(XoxoRedemption $redemption): self
    {
        return $this->state(function() use ($redemption) {
            return [
                'object_id'   => $redemption,
                'object_type' => Relation::getAliasByModel(get_class($redemption)),
            ];
        });
    }

    public function orderCanceled(): self
    {
        return $this->state(function() {
            return [
                'action'        => Point::ACTION_ORDER_CANCELED,
                'points_earned' => $this->faker->numberBetween(-1, -100),
            ];
        });
    }

    public function redeemed(): self
    {
        return $this->state(function() {
            return [
                'action'          => Point::ACTION_REDEEMED,
                'points_earned'   => 0,
                'points_redeemed' => $this->faker->numberBetween(1, 100),
            ];
        });
    }
}
