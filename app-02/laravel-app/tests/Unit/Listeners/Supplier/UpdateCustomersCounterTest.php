<?php

namespace Tests\Unit\Listeners\Supplier;

use App\Events\Supplier\SupplierEventInterface;
use App\Jobs\Supplier\UpdateCustomersCounter as UpdateCustomersCounterJob;
use App\Listeners\Supplier\UpdateCustomersCounter;
use App\Models\Supplier;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdateCustomersCounterTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateCustomersCounter::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_an_update_customers_counter_job()
    {
        $this->refreshDatabaseForSingleTest();

        Bus::fake();

        $supplier = Supplier::factory()->createQuietly();

        $listener = new UpdateCustomersCounter();
        $listener->handle($this->supplierEventStub($supplier));

        Bus::assertDispatched(UpdateCustomersCounterJob::class);
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
