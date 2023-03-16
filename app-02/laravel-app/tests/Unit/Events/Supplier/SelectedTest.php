<?php

namespace Tests\Unit\Events\Supplier;

use App\Events\Supplier\Selected as SelectedEvent;
use App\Events\Supplier\SupplierEventInterface;
use App\Listeners\Supplier\SendSelectionNotification;
use App\Listeners\Supplier\UpdateTotalCustomers;
use App\Listeners\Supplier\UpdateCustomersCounter;
use App\Models\Supplier;
use ReflectionClass;
use Tests\TestCase;

class SelectedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(SelectedEvent::class);

        $this->assertTrue($reflection->implementsInterface(SupplierEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(SelectedEvent::class, [
            SendSelectionNotification::class,
            UpdateCustomersCounter::class,
            UpdateTotalCustomers::class,
        ]);
    }

    /** @test */
    public function it_returns_its_supplier()
    {
        $supplier = new Supplier();

        $event = new SelectedEvent($supplier);

        $this->assertSame($supplier, $event->supplier());
    }
}
