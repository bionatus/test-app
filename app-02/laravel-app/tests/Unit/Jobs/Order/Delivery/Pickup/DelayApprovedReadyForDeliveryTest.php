<?php

namespace Tests\Unit\Jobs\Order\Delivery\Pickup;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Jobs\Order\Delivery\Pickup\DelayApprovedReadyForDelivery;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class DelayApprovedReadyForDeliveryTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DelayApprovedReadyForDelivery::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new DelayApprovedReadyForDelivery(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_does_not_do_any_action_when_order_is_not_approved_awaiting_delivery()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('lastSubStatusIsAnyOf')
            ->with([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])
            ->once()
            ->andReturnFalse();

        $changeAction = Mockery::mock(ChangeStatus::class);
        $changeAction->shouldNotReceive('execute')->times(0)->withNoArgs();
        App::bind(ChangeStatus::class, fn() => $changeAction);

        $job = new DelayApprovedReadyForDelivery($order);
        $job->handle();
    }

    /** @test */
    public function it_executes_the_change_status_action_if_the_order_is_approved()
    {
        $this->refreshDatabaseForSingleTest();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('lastSubStatusIsAnyOf')
            ->with([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])
            ->once()
            ->andReturnTrue();

        $changeAction = Mockery::mock(ChangeStatus::class);
        $changeAction->shouldNotReceive('execute')->once()->withNoArgs();
        App::bind(ChangeStatus::class, fn() => $changeAction);

        $job = new DelayApprovedReadyForDelivery($order);
        $job->handle();
    }
}
