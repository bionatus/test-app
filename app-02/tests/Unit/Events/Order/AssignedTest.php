<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\Assigned as AssignedEvent;
use App\Events\Order\OrderEventInterface;
use App\Listeners\Order\SendAssignInAppNotification;
use App\Listeners\Order\SendAssignSmsNotification;
use App\Models\Order;
use App\Models\Supplier;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class AssignedTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(AssignedEvent::class);

        $this->assertTrue($reflection->implementsInterface(OrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(AssignedEvent::class, [
            SendAssignInAppNotification::class,
            SendAssignSmsNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $event = new AssignedEvent($order);

        $this->assertSame($order, $event->order());
    }
}
