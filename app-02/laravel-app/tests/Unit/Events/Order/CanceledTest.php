<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\Canceled;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\Delivery\Curri\RemoveUserDeliveryInformation;
use App\Listeners\Order\ProcessInvoiceOnCanceledOrder;
use App\Listeners\Order\RemovePointsOnCanceled;
use App\Listeners\Order\SendCanceledNotification;
use App\Listeners\Supplier\UpdateOutboundCounter;
use App\Listeners\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class CanceledTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(Canceled::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Canceled::class, [
            SendCanceledNotification::class,
            UpdateOutboundCounter::class,
            UpdatePendingApprovalOrdersCounter::class,
            ProcessInvoiceOnCanceledOrder::class,
            RemoveUserDeliveryInformation::class,
            RemovePointsOnCanceled::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new Canceled($order);

        $this->assertSame($order, $event->order());
    }
}
