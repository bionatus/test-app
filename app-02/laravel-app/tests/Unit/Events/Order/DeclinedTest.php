<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\Declined;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\SendDeclinedNotification;
use App\Listeners\Supplier\UpdateInboundCounter;
use App\Listeners\Supplier\UpdateOutboundCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class DeclinedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(Declined::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Declined::class, [
            SendDeclinedNotification::class,
            UpdateInboundCounter::class,
            UpdateOutboundCounter::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new Declined($order);

        $this->assertSame($order, $event->order());
    }
}
