<?php

namespace Tests\Unit\Jobs\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\Delivery\Curri\LegacyBook;
use App\Events\Order\Delivery\Curri\Booked;
use App\Jobs\Order\Delivery\Curri\LegacyDelayBooking;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class LegacyDelayBookingTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(LegacyDelayBooking::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new LegacyDelayBooking(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_does_not_do_any_action_when_order_is_not_approved()
    {
        Event::fake(Booked::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('isApproved')->withNoArgs()->once()->andReturnFalse();

        $bookAction = Mockery::mock(LegacyBook::class);
        $bookAction->shouldNotReceive('execute')->withNoArgs();
        App::bind(LegacyBook::class, fn() => $bookAction);

        $job = new LegacyDelayBooking($order);
        $job->handle();

        Event::assertNotDispatched(Booked::class);
    }

    /** @test */
    public function it_executes_the_book_action_if_the_order_is_approved()
    {
        Event::fake(Booked::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('isApproved')->withNoArgs()->once()->andReturnTrue();

        $bookAction = Mockery::mock(LegacyBook::class);
        $bookAction->shouldReceive('execute')->once()->andReturn();
        App::bind(LegacyBook::class, fn() => $bookAction);

        $job = new LegacyDelayBooking($order);
        $job->handle();
    }

    /** @test */
    public function it_dispatches_a_curri_booked_event_if_the_order_is_approved()
    {
        Event::fake(Booked::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('isApproved')->withNoArgs()->once()->andReturnTrue();

        $bookAction = Mockery::mock(LegacyBook::class);
        $bookAction->shouldReceive('execute')->once()->andReturn();
        App::bind(LegacyBook::class, fn() => $bookAction);

        $job = new LegacyDelayBooking($order);
        $job->handle();

        Event::assertDispatched(Booked::class);
    }
}
