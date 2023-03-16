<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\LegacyApproved as ApprovedEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\AddPoints;
use App\Listeners\Order\CreateOrderInvoice;
use App\Listeners\Order\DelayCompleteApprovedJob;
use App\Listeners\Order\Delivery\Curri\CalculateUserConfirmationTime;
use App\Listeners\Order\SendApprovedNotification;
use App\Listeners\OrderSnap\SaveOrderSnapInformation;
use App\Listeners\Supplier\UpdateOutboundCounter;
use App\Listeners\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class LegacyApprovedTest extends TestCase
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
            CreateOrderInvoice::class,
            UpdateOutboundCounter::class,
            UpdatePendingApprovalOrdersCounter::class,
            DelayCompleteApprovedJob::class,
            AddPoints::class,
            CalculateUserConfirmationTime::class,
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
