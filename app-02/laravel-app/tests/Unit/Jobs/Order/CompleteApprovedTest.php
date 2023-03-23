<?php

namespace Tests\Unit\Jobs\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Events\Order\LegacyCompleted;
use App\Jobs\Order\CompleteApproved;
use App\Models\Order;
use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CompleteApprovedTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CompleteApproved::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new CompleteApproved(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_dispatches_a_completed_event_and_calls_change_status_action_if_it_is_assigned_and_approved(
        bool $isAssigned,
        bool $isApproved,
        bool $expected
    ) {
        Event::fake(LegacyCompleted::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('isApproved')->withNoArgs()->times((int) $isAssigned)->andReturn($isApproved);
        $order->shouldReceive('isAssigned')->withNoArgs()->once()->andReturn($isAssigned);

        if ($expected) {
            $changeStatus = Mockery::mock(ChangeStatus::class);
            $changeStatus->shouldReceive('execute')->withNoArgs()->once()->andReturn($order);
            App::bind(ChangeStatus::class, fn() => $changeStatus);
        }

        (new CompleteApproved($order))->handle();

        if ($expected) {
            Event::assertDispatched(LegacyCompleted::class, function($event) use ($order) {
                return $event->order() == $order;
            });

            return;
        }

        Event::assertNothingDispatched();
    }

    public function dataProvider(): array
    {
        return [
            '!approved !assigned' => [false, false, false],
            '!approved assigned'  => [false, true, false],
            'approved !assigned'  => [true, false, false],
            'approved assigned'   => [true, true, true],
        ];
    }
}
