<?php

namespace Tests\Unit\Events\Order\Delivery\Curri;

use App\Events\Order\Delivery\Curri\UserConfirmationRequired;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\Delivery\Curri\SetUserDeliveryInformation;
use App\Models\Order;
use ReflectionClass;
use Tests\TestCase;

class UserConfirmationRequiredTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(UserConfirmationRequired::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(UserConfirmationRequired::class, [
            SetUserDeliveryInformation::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $order = new Order();

        $event = new UserConfirmationRequired($order);

        $this->assertSame($order, $event->order());
    }
}
