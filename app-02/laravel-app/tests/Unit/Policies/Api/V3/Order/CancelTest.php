<?php

namespace Tests\Unit\Policies\Api\V3\Order;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Api\V3\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_cancel_an_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->cancel($notOwner, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_owner_to_cancel_a_non_pending_or_pending_approval_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancel($owner, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }
}
