<?php

namespace Tests\Unit\Events\Order\Delivery\Curri;

use App\Events\Order\Delivery\Curri\Booked;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\Delivery\Curri\SetDeliverySupplierInformation;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class BookedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(Booked::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Booked::class, [
            SetDeliverySupplierInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new Booked($order);

        $this->assertSame($order, $event->order());
    }
}
