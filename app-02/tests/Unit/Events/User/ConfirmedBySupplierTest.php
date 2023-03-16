<?php

namespace Tests\Unit\Events\User;

use App\Events\Supplier\SupplierEventInterface;
use App\Events\User\ConfirmedBySupplier as ConfirmedEvent;
use App\Listeners\Supplier\UpdateCustomersCounter;
use App\Models\Supplier;
use ReflectionClass;
use Tests\TestCase;

class ConfirmedBySupplierTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ConfirmedEvent::class);

        $this->assertTrue($reflection->implementsInterface(SupplierEventInterface::class));
    }

    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(ConfirmedEvent::class, [
            UpdateCustomersCounter::class,
        ]);
    }

    /** @test */
    public function it_returns_its_supplier()
    {
        $supplier = new Supplier();

        $event = new ConfirmedEvent($supplier);

        $this->assertSame($supplier, $event->supplier());
    }
}
