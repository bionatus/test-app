<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\PointsEarned;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\SendPointsEarnedInAppNotification;
use App\Listeners\Order\SendPointsEarnedSmsNotification;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class PointsEarnedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(PointsEarned::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(PointsEarned::class, [
            SendPointsEarnedInAppNotification::class,
            SendPointsEarnedSmsNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new PointsEarned($order);

        $this->assertSame($order, $event->order());
    }
}
