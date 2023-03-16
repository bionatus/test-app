<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByOrderDeliveryType;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByOrderDeliveryTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider deliveryTypeProvider
     */
    public function it_filters_orders_by_order_delivery_type($type)
    {
        $supplier      = Supplier::factory()->createQuietly();
        $orderExpected = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($orderExpected)->create(['type' => $type]);

        $orderNotExpected = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($orderNotExpected)->create(['type' => 'other type']);

        $filtered = Order::scoped(new ByOrderDeliveryType($type))->first();

        $this->assertSame($orderExpected->getKey(), $filtered->first()->getKey());
    }

    public function deliveryTypeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY],
        ];
    }
}
