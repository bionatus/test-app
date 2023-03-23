<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\ApprovedByTeam as ApprovedByTeamEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\SendApprovedByTeamInAppNotification;
use App\Listeners\Order\SendApprovedByTeamSmsNotification;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class ApprovedByTeamTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ApprovedByTeamEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(ApprovedByTeamEvent::class, [
            SendApprovedByTeamInAppNotification::class,
            SendApprovedByTeamSmsNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new ApprovedByTeamEvent($order);

        $this->assertSame($order, $event->order());
    }
}
