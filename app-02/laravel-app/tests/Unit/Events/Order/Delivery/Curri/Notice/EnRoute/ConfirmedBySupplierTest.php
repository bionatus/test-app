<?php

namespace Tests\Unit\Events\Order\Delivery\Curri\Notice\EnRoute;

use App\Events\Order\Delivery\Curri\Notice\EnRoute\ConfirmedBySupplier;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\Delivery\Curri\RemoveSupplierDeliveryInformation;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class ConfirmedBySupplierTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ConfirmedBySupplier::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(ConfirmedBySupplier::class, [
            RemoveSupplierDeliveryInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new ConfirmedBySupplier($order);

        $this->assertSame($order, $event->order());
    }
}
