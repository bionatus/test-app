<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\OrderEvent;
use App\Events\Order\OrderEventInterface;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class OrderEventTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(OrderEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = $this->orderEventStub($order);

        $this->assertSame($order, $event->order());
    }

    private function orderEventStub($order): OrderEvent
    {
        return new class($order) extends OrderEvent {
        };
    }
}
