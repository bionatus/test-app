<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateExtraItemsInProgressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_update_extra_items()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $supplier         = Supplier::factory()->createQuietly();
        $order            = Order::factory()->usingSupplier($supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->updateExtraItemsInProgress($anotherProcessor, $order));
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_allows_the_processor_to_update_extra_items_if_the_order_is_not_in_the_correct_status(
        int $orderSubstatusId,
        bool $expected
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($orderSubstatusId)->create();

        $policy = new OrderPolicy();

        $this->assertSame($expected, $policy->updateExtraItemsInProgress($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, true],
            [Substatus::STATUS_APPROVED_DELIVERED, true],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_ABORTED, false],
            [Substatus::STATUS_CANCELED_CANCELED, false],
            [Substatus::STATUS_CANCELED_DECLINED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, false],
            [Substatus::STATUS_CANCELED_DELETED_USER, false],
        ];
    }
}
