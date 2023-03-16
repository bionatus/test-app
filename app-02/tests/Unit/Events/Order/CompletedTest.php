<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\Completed as CompletedEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\AddPoints;
use App\Listeners\Supplier\UpdateOutboundCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class CompletedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(CompletedEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(CompletedEvent::class, [
            UpdateOutboundCounter::class,
            AddPoints::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new CompletedEvent($order);

        $this->assertSame($order, $event->order());
    }
}
