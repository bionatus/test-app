<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_the_owner_to_update_an_order_with_curri_delivery()
    {
        $owner = Staff::factory()->createQuietly();
        $order = Order::factory()->approved()->usingSupplier($owner->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        OrderStaff::factory()->usingOrder($order)->usingStaff($owner)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($owner, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_another_user_to_update_an_order_with_order_deliveries_pickup_and_shipment_and_valid_substatuses(
        string $deliveryType,
        int $substatusId,
    ) {
        $notOwner = Staff::factory()->createQuietly();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();
        OrderDelivery::factory()->usingOrder($order)->create([
            'type' => $deliveryType,
        ]);
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderStaff::factory()->usingOrder($order)->usingStaff($notOwner)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($notOwner, $order));
    }

    public function dataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_PICKUP, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, Substatus::STATUS_APPROVED_DELIVERED],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
        ];
    }

    /**
     * @test
     * @dataProvider validSubstatusesPickupProvider
     */
    public function it_allows_owner_to_update_an_order_with_order_delivery_pickup_and_valid_substatuses(
        int $substatusId,
    ) {
        $owner = Staff::factory()->createQuietly();
        $order = Order::factory()->approved()->usingSupplier($owner->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderStaff::factory()->usingOrder($order)->usingStaff($owner)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->complete($owner, $order));
    }

    public function validSubstatusesPickupProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [Substatus::STATUS_APPROVED_DELIVERED],
        ];
    }

    /** @test */
    public function it_allows_owner_to_update_an_order_with_order_delivery_shipment_and_substatus_approved_awaiting_delivery(
    )
    {
        $owner = Staff::factory()->createQuietly();
        $order = Order::factory()->approved()->usingSupplier($owner->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
            ->create();
        OrderStaff::factory()->usingOrder($order)->usingStaff($owner)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->complete($owner, $order));
    }
}
