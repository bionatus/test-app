<?php

namespace Tests\Unit\Events\User;

use App\Events\Supplier\SupplierEventInterface;
use App\Events\User\RestoredBySupplier;
use App\Listeners\Supplier\UpdateCustomersCounter;
use App\Models\Supplier;
use ReflectionClass;
use Tests\TestCase;

class RestoredBySupplierTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(RestoredBySupplier::class);

        $this->assertTrue($reflection->implementsInterface(SupplierEventInterface::class));
    }

    /** @test */
    public function it_has_a_send_new_technician_notification_listener()
    {
        $this->assertEventHasListeners(RestoredBySupplier::class, [
            UpdateCustomersCounter::class,
        ]);
    }

    /** @test */
    public function it_returns_its_supplier()
    {
        $supplier = new Supplier();

        $event = new RestoredBySupplier($supplier);

        $this->assertSame($supplier, $event->supplier());
    }
}
