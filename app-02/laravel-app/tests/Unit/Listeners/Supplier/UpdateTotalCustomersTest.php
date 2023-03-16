<?php

namespace Tests\Unit\Listeners\Supplier;

use App\Events\Supplier\SupplierEventInterface;
use App\Jobs\Supplier\UpdateTotalCustomers as UpdateTotalCustomersJob;
use App\Listeners\Supplier\UpdateTotalCustomers;
use App\Models\Supplier;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdateTotalCustomersTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateTotalCustomers::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_an_update_total_customers_job()
    {
        $this->refreshDatabaseForSingleTest();

        Bus::fake();

        $supplier = Supplier::factory()->createQuietly();

        $listener = new UpdateTotalCustomers();
        $listener->handle($this->supplierEventStub($supplier));

        Bus::assertDispatched(UpdateTotalCustomersJob::class);
    }

    private function supplierEventStub(Supplier $supplier): SupplierEventInterface
    {
        return new class($supplier) implements SupplierEventInterface {
            private Supplier $supplier;

            public function __construct(Supplier $supplier)
            {
                $this->supplier = $supplier;
            }

            public function supplier(): Supplier
            {
                return $this->supplier;
            }
        };
    }
}
