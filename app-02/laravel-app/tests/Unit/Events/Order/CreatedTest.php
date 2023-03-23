<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\Created as CreatedEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\SendCreatedNotification;
use App\Listeners\OrderSnap\SaveOrderSnapInformation;
use App\Listeners\Supplier\LogOrderIntoMissedOrderRequest;
use App\Listeners\Supplier\UpdateInboundCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class CreatedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(CreatedEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(CreatedEvent::class, [
            SendCreatedNotification::class,
            UpdateInboundCounter::class,
            LogOrderIntoMissedOrderRequest::class,
            SaveOrderSnapInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new CreatedEvent($order);

        $this->assertSame($order, $event->order());
    }
}
