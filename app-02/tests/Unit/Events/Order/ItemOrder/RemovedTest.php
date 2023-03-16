<?php

namespace Tests\Unit\Events\Order\ItemOrder;

use App\Events\Order\ItemOrder\ItemOrderEventInterface;
use App\Events\Order\ItemOrder\Removed as RemovedEvent;
use App\Listeners\Order\ItemOrder\RemovePointsOnRemoved;
use App\Models\ItemOrder;
use ReflectionClass;
use Tests\TestCase;

class RemovedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(RemovedEvent::class);

        $this->assertTrue($reflection->implementsInterface(ItemOrderEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(RemovedEvent::class, [
            RemovePointsOnRemoved::class,
        ]);
    }

    /** @test */
    public function it_returns_its_order()
    {
        $itemOrder = new ItemOrder();

        $event = new RemovedEvent($itemOrder);

        $this->assertSame($itemOrder, $event->itemOrder());
    }
}
