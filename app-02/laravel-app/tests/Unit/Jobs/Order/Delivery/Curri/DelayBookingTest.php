<?php

namespace Tests\Unit\Jobs\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Actions\Models\Order\Delivery\Curri\Book;
use App\Events\Order\Delivery\Curri\Booked;
use App\Jobs\Order\Delivery\Curri\DelayBooking;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class DelayBookingTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DelayBooking::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new DelayBooking(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_does_not_do_any_action_when_order_is_not_approved_awaiting_delivery()
    {
        Event::fake(Booked::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('lastSubStatusIsAnyOf')
            ->with([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])
            ->once()
            ->andReturnFalse();

        $job = new DelayBooking($order);
        $job->handle();

        $bookAction = Mockery::mock(Book::class);
        $bookAction->shouldNotReceive('execute')->withNoArgs();
        App::bind(Book::class, fn() => $bookAction);

        Event::assertNotDispatched(Booked::class);
    }

    /** @test */
    public function it_executes_the_book_and_change_status_actions_if_the_order_is_approved()
    {
        $this->refreshDatabaseForSingleTest();
        Event::fake(Booked::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('lastSubStatusIsAnyOf')
            ->with([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])
            ->once()
            ->andReturnTrue();

        $bookAction = Mockery::mock(Book::class);
        $bookAction->shouldReceive('execute')->once()->andReturn();
        App::bind(Book::class, fn() => $bookAction);

        $changeAction = Mockery::mock(ChangeStatus::class);
        $changeAction->shouldReceive('execute')->once()->andReturn();
        App::bind(ChangeStatus::class, fn() => $changeAction);

        $job = new DelayBooking($order);
        $job->handle();
    }

    /** @test */
    public function it_dispatches_a_curri_booked_event_if_the_order_is_approved()
    {
        Event::fake(Booked::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('lastSubStatusIsAnyOf')
            ->with([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])
            ->once()
            ->andReturnTrue();

        $bookAction = Mockery::mock(Book::class);
        $bookAction->shouldReceive('execute')->once()->andReturn();
        App::bind(Book::class, fn() => $bookAction);

        $changeAction = Mockery::mock(ChangeStatus::class);
        $changeAction->shouldReceive('execute')->once()->andReturn();
        App::bind(ChangeStatus::class, fn() => $changeAction);

        $job = new DelayBooking($order);
        $job->handle();

        Event::assertDispatched(Booked::class);
    }
}
