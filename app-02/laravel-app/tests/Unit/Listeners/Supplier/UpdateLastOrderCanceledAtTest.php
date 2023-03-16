<?php

namespace Tests\Unit\Listeners\Supplier;

use App\Events\Order\OrderEventInterface;
use App\Jobs\Supplier\UpdateLastOrderCanceledAt as UpdateLastOrderCanceledAtJob;
use App\Listeners\Supplier\UpdateLastOrderCanceledAt;
use App\Models\Order;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdateLastOrderCanceledAtTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateLastOrderCanceledAt::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_an_update_last_order_canceled_at_job()
    {
        $this->refreshDatabaseForSingleTest();

        Bus::fake();

        $order = Order::factory()->createQuietly();

        $listener = new UpdateLastOrderCanceledAt();
        $listener->handle($this->orderEventStub($order));

        Bus::assertDispatched(UpdateLastOrderCanceledAtJob::class);
    }

    private function orderEventStub(Order $order): OrderEventInterface
    {
        return new class($order) implements OrderEventInterface {
            private Order $order;

            public function __construct(Order $order)
            {
                $this->order = $order;
            }

            public function order(): Order
            {
                return $this->order;
            }
        };
    }
}
