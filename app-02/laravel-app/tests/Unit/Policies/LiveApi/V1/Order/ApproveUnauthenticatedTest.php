<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproveUnauthenticatedTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     *
     * @dataProvider provider
     */
    public function it_allows_to_approve_a_pending_approval_order(int $substatusId, bool $expected)
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expected, $policy->approveUnauthenticated(Staff::factory()->createQuietly(), $order));
    }

    public function provider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }
}
