<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByLastSubstatusesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_orders_by_last_substatuses()
    {
        $supplier         = Supplier::factory()->createQuietly();
        $orderExpected    = Order::factory()->pending()->usingSupplier($supplier)->create();
        $orderNotExpected = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->usingOrder($orderExpected)
            ->create();
        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_CANCELED_ABORTED)
            ->usingOrder($orderNotExpected)
            ->create();

        $filtered = Order::scoped(new ByLastSubstatuses(array_merge(Substatus::STATUSES_PENDING,
            Substatus::STATUSES_PENDING_APPROVAL)))->get();

        $this->assertSame($orderExpected->getKey(), $filtered->first()->getKey());
    }
}
