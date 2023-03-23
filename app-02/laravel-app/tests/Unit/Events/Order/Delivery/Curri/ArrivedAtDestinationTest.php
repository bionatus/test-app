<?php

namespace Tests\Unit\Events\Order\Delivery\Curri;

use App\Events\Order\Delivery\Curri\ArrivedAtDestination;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\Delivery\Curri\DelayCompleteDoneJob;
use App\Listeners\User\SendCurriDeliveryArrivedAtDestinationInAppNotification;
use App\Listeners\User\SendCurriDeliveryArrivedAtDestinationSmsNotification;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class ArrivedAtDestinationTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ArrivedAtDestination::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(ArrivedAtDestination::class, [
            SendCurriDeliveryArrivedAtDestinationInAppNotification::class,
            SendCurriDeliveryArrivedAtDestinationSmsNotification::class,
            DelayCompleteDoneJob::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new ArrivedAtDestination($order);

        $this->assertSame($order, $event->order());
    }
}
