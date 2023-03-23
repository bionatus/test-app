<?php

namespace Tests\Unit\Policies\LiveApi\V1\ItemOrder;

use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V1\ItemOrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveItemInProgressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_processor_to_remove_an_item_from_an_order()
    {
        $notProcessor = Staff::factory()->createQuietly();
        $order        = Order::factory()->approved()->createQuietly();
        $itemOrder    = ItemOrder::factory()->usingOrder($order)->create();

        $policy = new ItemOrderPolicy();

        $this->assertFalse($policy->removeItemOrderInProgress($notProcessor, $itemOrder));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_remove_an_item_from_a_non_approved_or_non_completed_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();

        $policy = new ItemOrderPolicy();

        $this->assertEquals($expectedResult, $policy->removeItemOrderInProgress($processor, $itemOrder));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_COMPLETED_DONE, true],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }

    /** @test */
    public function it_verifies_item_order_status_is_not_in_removed()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $notProcessor = Staff::factory()->usingSupplier($supplier)->create();
        $order        = Order::factory()->approved()->usingSupplier($supplier)->create();
        $itemOrder    = ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_REMOVED]);
        $policy       = new ItemOrderPolicy();

        $this->assertFalse($policy->removeItemOrderInProgress($notProcessor, $itemOrder));
    }

    /** @test */
    public function it_pass_if_conditions_are_met()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();
        $policy    = new ItemOrderPolicy();

        $this->assertTrue($policy->removeItemOrderInProgress($processor, $itemOrder));
    }
}
