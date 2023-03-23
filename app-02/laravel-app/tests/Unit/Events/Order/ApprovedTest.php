<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\Approved as ApprovedEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\CreateOrderInvoice;
use App\Listeners\Order\SendApprovedNotification;
use App\Listeners\Order\SendChatApprovedNotification;
use App\Listeners\OrderSnap\SaveOrderSnapInformation;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class ApprovedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ApprovedEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(ApprovedEvent::class, [
            SendApprovedNotification::class,
            SendChatApprovedNotification::class,
            CreateOrderInvoice::class,
            SaveOrderSnapInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new ApprovedEvent($order);

        $this->assertSame($order, $event->order());
    }
}
