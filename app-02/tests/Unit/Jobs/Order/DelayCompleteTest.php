<?php

namespace Tests\Unit\Jobs\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Events\Order\Completed;
use App\Jobs\Order\DelayComplete;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class DelayCompleteTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DelayComplete::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new DelayComplete(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_calls_change_status_action_and_dispatches_a_complete_job()
    {
        Event::fake(Completed::class);

        $changeStatus = Mockery::mock(ChangeStatus::class);
        $changeStatus->shouldReceive('execute')->withNoArgs()->once();
        App::bind(ChangeStatus::class, fn() => $changeStatus);

        $orderSubstatus = Mockery::mock(App\Models\OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus_id')->once()->andReturn(320);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('fresh')->withNoArgs()->once()->andReturnSelf();
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);

        $job = new DelayComplete($order);
        $job->handle();

        Event::assertDispatched(Completed::class);
    }
}
