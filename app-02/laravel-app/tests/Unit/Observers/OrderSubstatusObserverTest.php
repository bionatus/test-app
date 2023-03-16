<?php

namespace Tests\Unit\Observers;

use App\Events\Supplier\AccessedPriceAndAvailability;
use App\Events\Supplier\AccessedWillCall;
use App\Jobs\LogActivity;
use App\Jobs\Order\SetTotalOrdersInformationNewStatuses;
use App\Jobs\Supplier\SetPriceAndAvailability;
use App\Jobs\Supplier\SetTotalActiveOrders;
use App\Jobs\Supplier\SetWillCall;
use App\Models\Activity;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Observers\OrderSubstatusObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Tests\TestCase;

class OrderSubstatusObserverTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    /** @test */
    public function it_deletes_the_last_activity_log_for_status_order_when_order_changing_the_status()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->createQuietly();
        $activity = Activity::factory()->orders()->usingSubject($order)->create(['description' => 'order.updated']);

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        $this->assertDatabaseMissing(Activity::tableName(), [
            'id' => $activity->getKey(),
        ]);
    }

    /** @test */
    public function it_dispatched_log_activity_job()
    {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->createQuietly();

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        Bus::assertDispatched(LogActivity::class);
    }

    /** @test */
    public function it_dispatches_set_total_orders_information_new_statuses_job()
    {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->create();

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        Bus::assertDispatched(SetTotalOrdersInformationNewStatuses::class);
    }

    /** @test */
    public function it_does_not_dispatches_set_total_orders_information_new_statuses_job_when_order_has_no_user()
    {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->create(['user_id' => null]);

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        Bus::assertNotDispatched(SetTotalOrdersInformationNewStatuses::class);
    }

    /**
     * @test
     * @dataProvider dataProviderPriceAndAvailability
     */
    public function it_dispatches_set_price_and_availability_job_when_substatus_is_pending_requested(
        int $substatusId,
        bool $expected
    ) {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->createQuietly();

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        $expected ? Bus::assertDispatched(SetPriceAndAvailability::class) : Bus::assertNotDispatched(SetPriceAndAvailability::class);
    }

    public function dataProviderPriceAndAvailability(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, false],
            [Substatus::STATUS_CANCELED_ABORTED, false],
            [Substatus::STATUS_CANCELED_CANCELED, false],
            [Substatus::STATUS_CANCELED_DECLINED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, false],
            [Substatus::STATUS_CANCELED_DELETED_USER, false],
            [Substatus::STATUS_COMPLETED_DONE, false],

        ];
    }

    /**
     * @test
     * @dataProvider dataProviderWillCall
     */
    public function it_dispatches_set_will_call_job_when_expectations_are_met(
        ?int $previousSubstatusId,
        int $currentSubstatusId,
        bool $expected
    ) {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        if ($previousSubstatusId) {
            OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($previousSubstatusId)->createQuietly();
        }
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($currentSubstatusId)->createQuietly();

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        $expected ? Bus::assertDispatched(SetWillCall::class) : Bus::assertNotDispatched(SetWillCall::class);
    }

    public function dataProviderWillCall(): array
    {
        return [
            [null, Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_REQUESTED, Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, Substatus::STATUS_APPROVED_DELIVERED, false],
            [Substatus::STATUS_APPROVED_DELIVERED, Substatus::STATUS_COMPLETED_DONE, false],
        ];
    }

    /** @test */
    public function it_calls_order_touch_method_when_an_order_delivery_is_created()
    {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus_id')->once()->andReturn(100);
        $orderSubstatus->shouldReceive('isWillCall')->withNoArgs()->once()->andReturn(false);

        $observer = new OrderSubstatusObserver();

        $observer->created($orderSubstatus);
    }

    /** @test */
    public function it_dispatches_set_total_active_orders_job()
    {
        Bus::fake();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->create();

        $observer = new OrderSubstatusObserver();
        $observer->created($order->lastStatus);

        Bus::assertDispatched(SetTotalActiveOrders::class);
    }
}
