<?php

namespace Tests\Unit\Events\Supplier;

use App\Events\Supplier\SupplierEventInterface;
use App\Events\Supplier\Unselected as UnselectedEvent;
use App\Listeners\Supplier\UpdateCustomersCounter;
use App\Listeners\Supplier\UpdateTotalCustomers;
use App\Models\Supplier;
use ReflectionClass;
use Tests\TestCase;

class UnselectedTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(UnselectedEvent::class);

        $this->assertTrue($reflection->implementsInterface(SupplierEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(UnselectedEvent::class, [
            UpdateCustomersCounter::class,
            UpdateTotalCustomers::class,
        ]);
    }

    /** @test */
    public function it_returns_its_supplier()
    {
        $supplier = new Supplier();

        $event = new UnselectedEvent($supplier);

        $this->assertSame($supplier, $event->supplier());
    }
}
