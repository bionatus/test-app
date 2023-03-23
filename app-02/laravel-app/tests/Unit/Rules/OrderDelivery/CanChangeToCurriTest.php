<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Models\WarehouseDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\Supplier;
use App\Rules\OrderDelivery\CanChangeToCurri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanChangeToCurriTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider typeProvider
     */
    public function it_returns_true_unless_type_is_curri(string $newType, bool $valid)
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        $rule = new CanChangeToCurri($order);

        $this->assertSame($valid, $rule->passes('attribute', $newType));
    }

    public function typeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_OTHER_DELIVERY, true],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, true],
            [OrderDelivery::TYPE_PICKUP, true],
        ];
    }

    /** @test */
    public function it_returns_false_if_deliverable_does_not_have_destination_address()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['destination_address_id' => null]);

        $rule = new CanChangeToCurri($order);

        $this->assertFalse($rule->passes('attribute', OrderDelivery::TYPE_CURRI_DELIVERY));
    }

    /** @test */
    public function it_returns_true_if_deliverable_has_destination_address()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->warehouseDelivery()->create();
        WarehouseDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $rule = new CanChangeToCurri($order);

        $this->assertTrue($rule->passes('attribute', OrderDelivery::TYPE_CURRI_DELIVERY));
    }

    /** @test */
    public function it_has_custom_message()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        $rule = new CanChangeToCurri($order);

        $this->assertEquals('It cannot change to Curri because destination address does not exist.', $rule->message());
    }
}
