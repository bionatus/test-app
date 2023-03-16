<?php

namespace Tests\Unit\Policies\Api\V3\Order;

use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Api\V3\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ConfirmCurriOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_owner_to_confirm_a_curri_delivery()
    {
        $owner         = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($owner)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->confirmCurriOrder($owner, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_confirm_a_curri_delivery()
    {
        $notOwner      = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->approved()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmCurriOrder($notOwner, $order));
    }

    /**
     * @test
     * @dataProvider orderDataProvider
     */
    public function it_disallows_the_owner_to_confirm_a_curri_delivery_when_the_order_is_not_approved(
        int $substatusId,
        bool $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($owner)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmCurriOrder($owner, $order));
    }

    public function orderDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }

    /**
     * @test
     * @dataProvider orderDeliveryDataProvider
     */
    public function it_disallows_the_owner_to_confirm_a_non_curri_delivery(
        string $orderDeliveryType,
        bool $expectedResult
    ) {
        $owner         = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($owner)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            'type'       => $orderDeliveryType,
        ]);

        if ($orderDeliveryType === OrderDelivery::TYPE_CURRI_DELIVERY) {
            CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();
        }

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmCurriOrder($owner, $order->refresh()));
    }

    public function orderDeliveryDataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, false],
            [OrderDelivery::TYPE_PICKUP, false],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, false],
        ];
    }

    /** @test */
    public function it_disallows_the_owner_to_confirm_a_curri_delivery_already_confirmed_by_user()
    {
        $owner         = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->approved()->usingUser($owner)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()
            ->usingOrderDelivery($orderDelivery)
            ->confirmedBySupplier()
            ->confirmedByUser()
            ->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmCurriOrder($owner, $order));
    }
}
