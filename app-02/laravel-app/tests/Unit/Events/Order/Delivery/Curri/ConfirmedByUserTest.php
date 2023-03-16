<?php

namespace Tests\Unit\Events\Order\Delivery\Curri;

use App\Events\Order\Delivery\Curri\ConfirmedByUser;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\Delivery\Curri\RemoveUserDeliveryInformation;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class ConfirmedByUserTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ConfirmedByUser::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(ConfirmedByUser::class, [
            RemoveUserDeliveryInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new ConfirmedByUser($order);

        $this->assertSame($order, $event->order());
    }
}
