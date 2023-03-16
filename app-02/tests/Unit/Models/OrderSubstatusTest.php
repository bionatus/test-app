<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Status;
use App\Models\Substatus;
use Carbon\Carbon;

class OrderSubstatusTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OrderSubstatus::tableName(), [
            'id',
            'order_id',
            'substatus_id',
            'detail',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_if_is_pending()
    {
        $orderSubstatusNotPending = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create();
        $orderSubstatusPending1   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)
            ->create();
        $orderSubstatusPending2   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_ASSIGNED)
            ->create();

        $this->assertFalse($orderSubstatusNotPending->isPending());
        $this->assertTrue($orderSubstatusPending1->isPending());
        $this->assertTrue($orderSubstatusPending2->isPending());
    }

    /** @test */
    public function it_knows_if_is_will_call()
    {
        $orderSubstatusNotWillCall = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)
            ->create();
        $orderSubstatusWillCallOne = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();
        $orderSubstatusWillCallTwo = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_APPROVED_READY_FOR_DELIVERY)
            ->create();

        $this->assertFalse($orderSubstatusNotWillCall->isWillCall());
        $this->assertTrue($orderSubstatusWillCallOne->isWillCall());
        $this->assertTrue($orderSubstatusWillCallTwo->isWillCall());
    }

    /** @test */
    public function it_knows_if_is_pending_approval()
    {
        $orderSubstatusNotPendingApproval = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)
            ->create();
        $orderSubstatusPendingApproval1   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create();
        $orderSubstatusPendingApproval2   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();
        $orderSubstatusPendingApproval3   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED)
            ->create();

        $this->assertFalse($orderSubstatusNotPendingApproval->isPendingApproval());
        $this->assertTrue($orderSubstatusPendingApproval1->isPendingApproval());
        $this->assertTrue($orderSubstatusPendingApproval2->isPendingApproval());
        $this->assertTrue($orderSubstatusPendingApproval3->isPendingApproval());
    }

    /** @test */
    public function it_knows_if_is_approved()
    {
        $orderSubstatusNotApproved = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)
            ->create();
        $orderSubstatusApproved1   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
            ->create();
        $orderSubstatusApproved2   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_APPROVED_READY_FOR_DELIVERY)
            ->create();
        $orderSubstatusApproved3   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_APPROVED_DELIVERED)
            ->create();

        $this->assertFalse($orderSubstatusNotApproved->isApproved());
        $this->assertTrue($orderSubstatusApproved1->isApproved());
        $this->assertTrue($orderSubstatusApproved2->isApproved());
        $this->assertTrue($orderSubstatusApproved3->isApproved());
    }

    /** @test */
    public function it_knows_if_is_canceled()
    {
        $orderSubstatusNotCanceled = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)
            ->create();
        $orderSubstatusCanceled1   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_ABORTED)
            ->create();
        $orderSubstatusCanceled2   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_CANCELED)
            ->create();
        $orderSubstatusCanceled3   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_DECLINED)
            ->create();
        $orderSubstatusCanceled4   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_REJECTED)
            ->create();
        $orderSubstatusCanceled5   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_BLOCKED_USER)
            ->create();
        $orderSubstatusCanceled6   = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_DELETED_USER)
            ->create();

        $this->assertFalse($orderSubstatusNotCanceled->isCanceled());
        $this->assertTrue($orderSubstatusCanceled1->isCanceled());
        $this->assertTrue($orderSubstatusCanceled2->isCanceled());
        $this->assertTrue($orderSubstatusCanceled3->isCanceled());
        $this->assertTrue($orderSubstatusCanceled4->isCanceled());
        $this->assertTrue($orderSubstatusCanceled5->isCanceled());
        $this->assertTrue($orderSubstatusCanceled6->isCanceled());
    }

    /** @test */
    public function it_knows_if_is_completed()
    {
        $orderSubstatusNotCompleted = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create();
        $orderSubstatusCompleted    = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_COMPLETED_DONE)
            ->create();

        $this->assertFalse($orderSubstatusNotCompleted->isCompleted());
        $this->assertTrue($orderSubstatusCompleted->isCompleted());
    }

    /** @test */
    public function it_knows_the_status_name()
    {
        $orderSubstatus = OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create();
        $expected       = Status::STATUS_NAME_PENDING_APPROVAL;
        $this->assertSame($expected, $orderSubstatus->getStatusName());
    }

    /** @test */
    public function it_updates_the_updated_at_field_from_order_when_creating_an_order_substatus()
    {
        $updated_at     = Carbon::now()->subDays(4);
        $order          = Order::factory()->create(['updated_at' => $updated_at]);
        $orderSubstatus = OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create();
        $order->refresh();

        $this->assertEquals(Carbon::now()->startOfSecond(), $orderSubstatus->updated_at);
        $this->assertNotEquals($order->updated_at, $updated_at);
    }

    /** @test */
    public function it_updates_the_updated_at_field_from_order_when_updating_an_order_substatus()
    {
        $updated_at        = Carbon::now()->subDays(4);
        $order             = Order::factory()->create();
        $orderSubstatus    = OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create([
                'updated_at' => $updated_at,
            ]);
        $order->updated_at = $updated_at;
        $order->save();

        $now                        = Carbon::now()->startOfSecond();
        $orderSubstatus->updated_at = $now;
        $orderSubstatus->save();
        $order->refresh();

        $this->assertEquals($now, $order->updated_at);
        $this->assertEquals($now, $orderSubstatus->updated_at);
    }
}
