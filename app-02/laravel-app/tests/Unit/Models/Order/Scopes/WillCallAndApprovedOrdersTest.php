<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\WillCallAndApprovedOrders;
use App\Models\OrderDelivery;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class WillCallAndApprovedOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_orders_when_supplier_action_is_needed_and_the_orders_are_advance()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $orderExpected = Collection::make([]);

        $orderExpected->add(Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->usingSupplier($supplier)
            ->create());
        $orderExpected->add(Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_AWAITING_DELIVERY))
            ->usingSupplier($supplier)
            ->create());
        $orderExpected->add(Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_READY_FOR_DELIVERY))
            ->usingSupplier($supplier)
            ->create());
        $pickupOrder = Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->usingSupplier($supplier)
            ->create();
        OrderDelivery::factory()->usingOrder($pickupOrder)->create();
        $orderExpected->add($pickupOrder);

        $anotherOrder1 = Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->usingSupplier($supplier)
            ->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($anotherOrder1);
        $anotherOrder2 = Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->usingSupplier($supplier)
            ->create();
        OrderDelivery::factory()->curriDelivery()->usingOrder($anotherOrder2);
        Order::factory()->usingSubstatus(Substatus::factory()->create())->usingSupplier($supplier)->create();

        $filtered = Order::scoped(new WillCallAndApprovedOrders())->pluck(Order::keyName());
        $this->assertCount(4, $filtered);
        $this->assertEqualsCanonicalizing($orderExpected->pluck(Order::keyName()), $filtered);
    }
}
