<?php

namespace Tests\Unit\Actions\Models\Order;

use App\Actions\Models\Order\LogPendingApprovalFirstView;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\PendingApprovalView;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogPendingApprovalFirstViewTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider dataProvider
     */
    public function it_logs_the_view_if_requirements_are_met(
        int $substatusId,
        bool $pendingApprovalViewExist,
        bool $expected
    ) {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(3)->create();

        if ($pendingApprovalViewExist) {
            PendingApprovalView::factory()->usingOrder($order)->create();
        }

        (new LogPendingApprovalFirstView($order, $user))->execute();

        if ($expected) {
            $this->assertDatabaseHas(PendingApprovalView::tableName(), [
                'order_id' => $order->getKey(),
                'user_id'  => $user->getKey(),
            ]);
        } else {
            $this->assertDatabaseMissing(PendingApprovalView::tableName(), [
                'order_id' => $order->getKey(),
                'user_id'  => $user->getKey(),
            ]);
        }
    }

    public function dataProvider(): array
    {
        return [
            //factory method, pendingApprovalViewExist, expected
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true, false],
            [Substatus::STATUS_CANCELED_DECLINED, false, false],
            [Substatus::STATUS_CANCELED_DECLINED, true, false],
            [Substatus::STATUS_COMPLETED_DONE, false, false],
            [Substatus::STATUS_COMPLETED_DONE, true, false],
            [Substatus::STATUS_PENDING_REQUESTED, false, false],
            [Substatus::STATUS_PENDING_REQUESTED, true, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true, false],
        ];
    }
}
