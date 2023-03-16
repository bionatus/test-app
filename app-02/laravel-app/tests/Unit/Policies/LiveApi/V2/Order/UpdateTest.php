<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\CurriDelivery;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\Staff;
use App\Models\Status;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_update_an_order()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $supplier         = Supplier::factory()->createQuietly();
        $order            = Order::factory()->usingSupplier($supplier)->pending()->create();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->update($anotherProcessor, $order));
    }

    /** @test
     * @dataProvider disallowDataProvider
     */
    public function it_disallows_the_processor_to_update_an_order_if_the_substatus_is_not_allowed_for_delivery(
        $type,
        $deliveryClassName
    ) {
        $processor     = Staff::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($processor->supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);
        $deliveryClassName::factory()->usingOrderDelivery($orderDelivery)->create();

        $status    = Status::factory()->create();
        $substatus = Substatus::factory()->usingStatus($status)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatus($substatus)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->update($processor, $order));
    }

    public function disallowDataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, CurriDelivery::class],
            [OrderDelivery::TYPE_PICKUP, Pickup::class],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, ShipmentDelivery::class],
        ];
    }

    /** @test */
    public function it_disallows_the_processor_to_update_an_order_if_the_order_does_not_have_media_and_its_quote_needed()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->create();
        OrderDelivery::factory()->usingOrder($order)->create();

        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->update($processor, $order));
    }

    /** @test
     * @dataProvider allowDataProvider
     */
    public function it_allows_the_processor_to_update_an_order($type, $deliveryClassName, $substatusId)
    {
        $processor     = Staff::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($processor->supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);
        $deliveryClassName::factory()->usingOrderDelivery($orderDelivery)->create();
        Media::factory()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->update($processor, $order));
    }

    public function allowDataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, CurriDelivery::class, Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED],
            [OrderDelivery::TYPE_CURRI_DELIVERY, CurriDelivery::class, Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
            [OrderDelivery::TYPE_CURRI_DELIVERY, CurriDelivery::class, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [OrderDelivery::TYPE_CURRI_DELIVERY, CurriDelivery::class, Substatus::STATUS_APPROVED_DELIVERED],
            [OrderDelivery::TYPE_PICKUP, Pickup::class, Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED],
            [OrderDelivery::TYPE_PICKUP, Pickup::class, Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, Pickup::class, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [
                OrderDelivery::TYPE_SHIPMENT_DELIVERY,
                ShipmentDelivery::class,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
            ],
            [
                OrderDelivery::TYPE_SHIPMENT_DELIVERY,
                ShipmentDelivery::class,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
            ],

        ];
    }
}
