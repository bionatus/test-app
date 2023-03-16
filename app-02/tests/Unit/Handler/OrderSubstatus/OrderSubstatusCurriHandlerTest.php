<?php

namespace Tests\Unit\Handler\OrderSubstatus;

use App;
use App\Actions\Models\Order\Delivery\Curri\Book;
use App\Handlers\OrderSubstatus\OrderSubstatusCurriHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use App\Jobs\Order\Delivery\Curri\DelayBooking;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use Bus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class OrderSubstatusCurriHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_is_order_substatus_changed_interface()
    {
        $reflection = new ReflectionClass(OrderSubstatusCurriHandler::class);

        $this->assertTrue($reflection->implementsInterface(OrderSubstatusUpdated::class));
    }

    /** @test
     * @dataProvider orderDeliveryProvider
     * @throws \Throwable
     */
    public function it_updates_an_order_substatus_to_awaiting_delivery_or_execute_book_action(
        bool $isNeededNow,
        int $minutes,
        int $expectedId
    ) {
        Carbon::setTestNow('2023-03-23 13:00:00');
        $order         = Order::factory()->createQuietly();
        $deliveryTime  = Carbon::now()->addMinutes($minutes);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => $deliveryTime->format('Y-m-d'),
            'start_time'    => $deliveryTime->format('H:i'),
            'is_needed_now' => $isNeededNow,
        ]);

        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        if ($expectedId == Substatus::STATUS_APPROVED_READY_FOR_DELIVERY) {
            $book = Mockery::mock(Book::class);
            $book->shouldReceive('execute')->withNoArgs()->times(1)->andReturn($order);
            App::bind(Book::class, fn() => $book);
        }

        $handler  = App::make(OrderSubstatusCurriHandler::class);
        $newOrder = $handler->processPendingApprovalQuoteNeeded($order);
        
        $this->assertEquals($expectedId, $newOrder->lastStatus->substatus_id);
    }

    public function orderDeliveryProvider(): array
    {
        return [
            //isNeededNow, minutes, $expectedId
            [true, 30, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [true, 100, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [false, 30, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [false, 100, Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
        ];
    }

    /** @test
     * @throws \Throwable
     */
    public function it_dispatch_the_delay_booking_job_when_requirements_satisfy()
    {
        Bus::fake();

        Carbon::setTestNow('2022-11-10 04:12AM');

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'          => Carbon::now(),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(18)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => false,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        $handler = App::make(OrderSubstatusCurriHandler::class);
        $handler->processPendingApprovalQuoteNeeded($order);

        Bus::assertDispatched(DelayBooking::class);
    }

    /** @test
     * @throws \Throwable
     */
    public function it_updates_delivery_date_time_if_delivery_is_outdated()
    {
        Bus::fake();

        Carbon::setTestNow('2022-11-10 01:00PM');

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => Carbon::now()->subDay()->format('Y-m-d'),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(16)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => false,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        $book = Mockery::mock(Book::class);
        $book->shouldReceive('execute')->withNoArgs()->once()->andReturn($order);
        App::bind(Book::class, fn() => $book);

        $handler = App::make(OrderSubstatusCurriHandler::class);
        $handler->processPendingApprovalQuoteNeeded($order);

        $this->assertDatabaseHas(OrderDelivery::class, [
            'date'       => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(13)->format('H:i'),
            'end_time'   => Carbon::createFromTime(14)->format('H:i'),
        ]);
    }

    /** @test
     * @throws \Throwable
     */
    public function it_does_not_updates_delivery_date_time_if_delivery_is_not_outdated()
    {
        Bus::fake();

        Carbon::setTestNow('2022-11-10 01:00PM');

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => Carbon::now()->format('Y-m-d'),
            'start_time'    => Carbon::createFromTime(14)->format('H:i'),
            'end_time'      => Carbon::createFromTime(15)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => true,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        $book = Mockery::mock(Book::class);
        $book->shouldReceive('execute')->withNoArgs()->once()->andReturn($order);
        App::bind(Book::class, fn() => $book);

        $handler = App::make(OrderSubstatusCurriHandler::class);
        $handler->processPendingApprovalQuoteNeeded($order);

        $this->assertDatabaseHas(OrderDelivery::class, [
            'date'       => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(14)->format('H:i'),
            'end_time'   => Carbon::createFromTime(15)->format('H:i'),
        ]);
    }
}
