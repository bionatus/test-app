<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\OrderEventInterface;
use App\Events\Order\SentForApproval as SentForApprovalEvent;
use App\Listeners\Order\SendSentForApprovalNotification;
use App\Listeners\OrderSnap\SaveOrderSnapInformation;
use App\Listeners\Supplier\UpdateInboundCounter;
use App\Listeners\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class SentForApprovalTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(SentForApprovalEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(SentForApprovalEvent::class, [
            SendSentForApprovalNotification::class,
            UpdateInboundCounter::class,
            UpdatePendingApprovalOrdersCounter::class,
            SaveOrderSnapInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new SentForApprovalEvent($order);

        $this->assertSame($order, $event->order());
    }
}
