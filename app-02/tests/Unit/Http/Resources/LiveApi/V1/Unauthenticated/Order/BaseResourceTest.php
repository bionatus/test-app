<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Unauthenticated\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\BaseResource;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\OrderDeliveryResource;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\SupplierResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $pickup = Mockery::mock(Pickup::class);

        $delivery = Mockery::mock(OrderDelivery::class);
        $delivery->shouldReceive('getAttribute')->with('type')->once()->andReturn('pickup');
        $delivery->shouldReceive('getAttribute')->with('requested_date')->once()->andReturn(Carbon::now());
        $delivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn(Carbon::createFromTime(9));
        $delivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn(Carbon::createFromTime(12));
        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('start_time')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('note')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('fee')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $delivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->with('quantity')->once()->andReturn($totalItems = 0);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'order uuid');
        $order->shouldReceive('getAttribute')->withArgs(['bid_number'])->once()->andReturn($bidNumber = 'ABC123');
        $order->shouldReceive('getAttribute')->withArgs(['discount'])->once()->andReturn($discount = 12.34);
        $order->shouldReceive('getAttribute')->withArgs(['tax'])->once()->andReturn($tax = 56.78);
        $order->shouldReceive('getAttribute')->withArgs(['supplier'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->withArgs(['orderDelivery'])->once()->andReturn($delivery);
        $order->shouldReceive('availableItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($subtotal = 55.33);

        $resource = new BaseResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'          => $uuid,
            'status'      => $status,
            'bid_number'  => $bidNumber,
            'supplier'    => null,
            'delivery'    => new OrderDeliveryResource($delivery),
            'total_items' => $totalItems,
            'discount'    => $discount,
            'tax'         => $tax,
            'total'       => $subtotal,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $pickup = Mockery::mock(Pickup::class);

        $delivery = Mockery::mock(OrderDelivery::class);
        $delivery->shouldReceive('getAttribute')->with('type')->once()->andReturn('pickup');
        $delivery->shouldReceive('getAttribute')->with('requested_date')->once()->andReturn(Carbon::now());
        $delivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn(Carbon::createFromTime(9));
        $delivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn(Carbon::createFromTime(12));
        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now());
        $delivery->shouldReceive('getAttribute')->with('start_time')->once()->andReturn(Carbon::createFromTime(15));
        $delivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturn(Carbon::createFromTime(18));
        $delivery->shouldReceive('getAttribute')->with('note')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('fee')->once()->andReturnNull();
        $delivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $delivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->with('quantity')->once()->andReturn($totalItems = 0);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier uuid');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->once()->andReturnNull();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'order uuid');
        $order->shouldReceive('getAttribute')->withArgs(['bid_number'])->once()->andReturn($bidNumber = 'ABC123');
        $order->shouldReceive('getAttribute')->withArgs(['discount'])->once()->andReturn($discount = 12.34);
        $order->shouldReceive('getAttribute')->withArgs(['tax'])->once()->andReturn($tax = 56.78);
        $order->shouldReceive('getAttribute')->withArgs(['supplier'])->twice()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->withArgs(['orderDelivery'])->once()->andReturn($delivery);
        $order->shouldReceive('availableItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($subtotal = 55.33);

        $resource = new BaseResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'          => $uuid,
            'status'      => $status,
            'bid_number'  => $bidNumber,
            'supplier'    => new SupplierResource($supplier),
            'delivery'    => new OrderDeliveryResource($delivery),
            'total_items' => $totalItems,
            'discount'    => $discount,
            'tax'         => $tax,
            'total'       => $subtotal,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
