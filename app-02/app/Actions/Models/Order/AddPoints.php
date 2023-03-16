<?php

namespace App\Actions\Models\Order;

use App;
use App\Events\Order\PointsEarned;
use App\Models\Order;
use App\Models\Point;
use App\Models\User;
use Exception;
use Throwable;

class AddPoints
{
    private Order $order;
    private string $action;
    private ?User $user;

    public function __construct(Order $order, string $action)
    {
        $this->order = $order;
        $this->action = $action;
        $this->user  = $order->user;
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        if (null === $this->user) {
            throw new Exception('Can not add points to order with deleted users');
        }

        $pointData = (App::make(CalculatePoints::class, ['order' => $this->order]))->execute();

        $this->user->points()->create([
            'object_type'   => $this->order::MORPH_ALIAS,
            'object_id'     => $this->order->getKey(),
            'action'        => $this->action,
            'coefficient'   => $pointData->coefficient(),
            'multiplier'    => $pointData->multiplier(),
            'points_earned' => $pointData->points(),
        ]);

        $this->user->processLevel();

        PointsEarned::dispatch($this->order);
    }
}
