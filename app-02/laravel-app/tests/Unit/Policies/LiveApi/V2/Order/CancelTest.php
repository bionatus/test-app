<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_processor_to_cancel_an_order()
    {
        $notProcessor = Staff::factory()->createQuietly();
        $order        = Order::factory()->pending()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->cancel($notProcessor, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_cancel_a_non_pending_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->createQuietly();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancel($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_ASSIGNED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }
}
