<?php

namespace Tests\Unit\Events\Order\ItemOrder;

use App\Events\Order\ItemOrder\ItemOrderEvent;
use App\Events\Order\ItemOrder\ItemOrderEventInterface;
use App\Models\ItemOrder;
use ReflectionClass;
use Tests\TestCase;

class ItemOrderEventTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ItemOrderEvent::class);

        $this->assertTrue($reflection->implementsInterface(ItemOrderEventInterface::class));
    }

    /** @test */
    public function it_returns_its_order()
    {
        $itemOrder = new ItemOrder();

        $event = $this->itemEventStub($itemOrder);

        $this->assertSame($itemOrder, $event->itemOrder());
    }

    private function itemEventStub($itemOrder): ItemOrderEvent
    {
        return new class($itemOrder) extends ItemOrderEvent {
        };
    }
}
