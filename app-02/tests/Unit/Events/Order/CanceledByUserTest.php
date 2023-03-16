<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\CanceledByUser as CanceledEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\SendCanceledByUserNotification;
use App\Listeners\Supplier\UpdateInboundCounter;
use App\Listeners\Supplier\UpdateLastOrderCanceledAt;
use App\Listeners\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class CanceledByUserTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(CanceledEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(CanceledEvent::class, [
            SendCanceledByUserNotification::class,
            UpdateInboundCounter::class,
            UpdateLastOrderCanceledAt::class,
            UpdatePendingApprovalOrdersCounter::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new CanceledEvent($order);

        $this->assertSame($order, $event->order());
    }
}
