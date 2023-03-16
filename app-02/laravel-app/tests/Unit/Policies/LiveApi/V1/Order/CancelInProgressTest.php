<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelInProgressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_processor_to_cancel_an_order()
    {
        $notProcessor = Staff::factory()->createQuietly();
        $order        = Order::factory()->approved()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->cancelInProgress($notProcessor, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_cancel_a_non_approved_or_non_completed_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancelInProgress($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, true],
            [Substatus::STATUS_APPROVED_DELIVERED, true],
            [Substatus::STATUS_COMPLETED_DONE, true],
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }
}
