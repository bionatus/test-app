<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\PriceAndAvailabilityRequests;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PriceAndAvailabilityRequestsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_orders_when_supplier_action_is_needed_and_the_orders_are_recently_created()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $orderExpected = Collection::make([]);

        $orderExpected->add(Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_REQUESTED))
            ->usingSupplier($supplier)
            ->create());
        $orderExpected->add(Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_ASSIGNED))
            ->usingSupplier($supplier)
            ->create());
        Order::factory()->usingSubstatus(Substatus::factory()->create())->usingSupplier($supplier)->create();

        $filtered = Order::scoped(new PriceAndAvailabilityRequests())->pluck(Order::keyName());
        $this->assertCount(2, $filtered);
        $this->assertEqualsCanonicalizing($orderExpected->pluck(Order::keyName()), $filtered);
    }
}
