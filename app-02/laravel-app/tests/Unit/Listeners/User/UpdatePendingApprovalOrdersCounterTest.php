<?php

namespace Tests\Unit\Listeners\User;

use App\Events\Order\OrderEventInterface;
use App\Jobs\User\UpdatePendingApprovalOrdersCounter as UpdatePendingApprovalOrdersCounterJob;
use App\Listeners\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Order;
use Bus;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdatePendingApprovalOrdersCounterTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_dispatches_an_update_pending_approval_orders_counter_job_when_user_exists()
    {
        $this->refreshDatabaseForSingleTest();

        Bus::fake();

        $order = Order::factory()->createQuietly();

        $listener = new UpdatePendingApprovalOrdersCounter();
        $listener->handle($this->orderEventStub($order));

        Bus::assertDispatched(UpdatePendingApprovalOrdersCounterJob::class);
    }

    /** @test */
    public function it_does_not_dispatch_an_update_pending_approval_orders_counter_job_when_user_not_exist()
    {
        $this->refreshDatabaseForSingleTest();

        Bus::fake();

        $order = Order::factory()->createQuietly(['user_id' => null]);

        $listener = new UpdatePendingApprovalOrdersCounter();
        $listener->handle($this->orderEventStub($order));

        Bus::assertNotDispatched(UpdatePendingApprovalOrdersCounterJob::class);
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
