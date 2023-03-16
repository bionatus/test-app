<?php

namespace Tests\Unit\Http\Resources\Api\V3\Order;

use App;
use App\Actions\Models\Order\CalculatePoints;
use App\Http\Resources\Api\V3\Order\DetailedResource;
use App\Http\Resources\Api\V3\Order\SupplierResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\Supplier;
use App\Types\Point;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(DetailedResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($itemOrdersCount = 0);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('Supplier name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturn('supplier address');
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('supplier address_2');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn('supplier city');
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturn('supplier phone');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturn('supplier country');
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('supplier zip code');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $pickup = Mockery::mock(Pickup::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn('pickup');
        $orderDelivery->shouldReceive('getAttribute')->with('requested_date')->once()->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn(Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'order uuid');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'Lorem Ipsum Name');
        $order->shouldReceive('getAttribute')->withArgs(['bid_number'])->once()->andReturn($bidNumber = '86546546');
        $order->shouldReceive('getAttribute')->withArgs(['supplier'])->twice()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($createdAt = Carbon::now());
        $order->shouldReceive('getAttribute')->withArgs(['discount'])->once()->andReturn($discount = 1);
        $order->shouldReceive('getAttribute')->withArgs(['tax'])->once()->andReturn($tax = 7);
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->withArgs(['orderDelivery'])->once()->andReturn($orderDelivery);
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($total = 100);
        $order->shouldReceive('getAttribute')
            ->withArgs(['working_on_it'])
            ->once()
            ->andReturn($workingOnIt = 'john doe');

        $pointData = Mockery::mock(Point::class);
        $pointData->shouldReceive('points')->withNoArgs()->once()->andReturn($points = 10);

        $calculatePoints = Mockery::mock(CalculatePoints::class);
        $calculatePoints->shouldReceive('execute')->withNoArgs()->once()->andReturn($pointData);
        App::bind(CalculatePoints::class, fn() => $calculatePoints);

        $resource = new DetailedResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'                   => $id,
            'status'               => $status,
            'name'                 => $name,
            'total'                => $total,
            'supplier'             => new SupplierResource($supplier),
            'total_items_quantity' => $itemOrdersCount,
            'bid_number'           => $bidNumber,
            'tax'                  => $tax,
            'discount'             => $discount,
            'delivery'             => new OrderDeliveryResource($orderDelivery),
            'created_at'           => $createdAt,
            'working_on_it'        => $workingOnIt,
            'points'               => $points,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($itemOrdersCount = 0);

        $pickup = Mockery::mock(Pickup::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn('pickup');
        $orderDelivery->shouldReceive('getAttribute')->with('requested_date')->once()->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn(Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'order uuid');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'Lorem Name');
        $order->shouldReceive('getAttribute')->withArgs(['bid_number'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->withArgs(['supplier'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->withArgs(['discount'])->once()->andReturn($discount = 1);
        $order->shouldReceive('getAttribute')->withArgs(['tax'])->once()->andReturn($tax = 7);
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->withArgs(['orderDelivery'])->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($createdAt = Carbon::now());
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($total = (float) 0);
        $order->shouldReceive('getAttribute')->withArgs(['working_on_it'])->once()->andReturnNull();

        $pointData = Mockery::mock(Point::class);
        $pointData->shouldReceive('points')->withNoArgs()->once()->andReturn($points = 10);

        $calculatePoints = Mockery::mock(CalculatePoints::class);
        $calculatePoints->shouldReceive('execute')->withNoArgs()->once()->andReturn($pointData);
        App::bind(CalculatePoints::class, fn() => $calculatePoints);

        $resource = new DetailedResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'                   => $id,
            'status'               => $status,
            'name'                 => $name,
            'total'                => $total,
            'supplier'             => null,
            'total_items_quantity' => $itemOrdersCount,
            'bid_number'           => null,
            'tax'                  => $tax,
            'discount'             => $discount,
            'delivery'             => new OrderDeliveryResource($orderDelivery),
            'created_at'           => $createdAt,
            'working_on_it'        => null,
            'points'               => $points,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
