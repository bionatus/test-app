<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendForApprovalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_send_an_order_to_pending_approval_fulfilled()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $supplier         = Supplier::factory()->createQuietly();
        $order            = Order::factory()->usingSupplier($supplier)->createQuietly();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_PENDING_ASSIGNED)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($anotherProcessor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_with_pending_items()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_PENDING_ASSIGNED)->create();
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_AVAILABLE]);
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_PENDING]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($processor, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_send_to_approve_an_order_if_the_order_is_not_in_pending_assigned(
        int $substatusId,
        bool $expectedResult
    ) {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->pending()->usingSupplier($supplier)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertSame($expectedResult, $policy->sendForApproval($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }

    /** @test */
    public function it_allows_the_processor_to_send_to_approve_an_order_with_pending_items()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_PENDING_ASSIGNED)->create();
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->sendForApproval($processor, $order));
    }
}
