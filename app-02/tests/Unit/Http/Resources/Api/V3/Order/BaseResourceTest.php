<?php

namespace Tests\Unit\Http\Resources\Api\V3\Order;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Http\Resources\Api\V3\Order\SupplierResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
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
    public function it_has_correct_fields()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('supplier name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturn('supplier address');
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('supplier address_2');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn('supplier city');
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturn('supplier contact phone');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturn('supplier country');
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('supplier zip code');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($itemOrdersCount = 0);

        $status = Mockery::mock(OrderSubstatus::class);
        $status->shouldReceive('getAttribute')->with('detail')->once()->andReturn($statusDetail = 'fake status detail');

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
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'order uuid');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($orderStatus = 'pending');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'a name');
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date = Carbon::now());
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')
            ->withArgs(['working_on_it'])
            ->once()
            ->andReturn($workingOnIt = 'jonh doe');
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($status);
        $resource = new BaseResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'            => $uuid,
            'status'        => $orderStatus,
            'status_detail' => $statusDetail,
            'name'          => $name,
            'supplier'      => new SupplierResource($supplier),
            'total_items'   => $itemOrdersCount,
            'channel'       => $channel,
            'created_at'    => $date,
            'working_on_it' => $workingOnIt,
            'delivery'      => new OrderDeliveryResource($orderDelivery),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('supplier name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturn('supplier address');
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('supplier address_2');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn('supplier city');
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturn('supplier contact phone');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturn('supplier country');
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('supplier zip code');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($itemOrdersCount = 0);

        $status = Mockery::mock(OrderSubstatus::class);
        $status->shouldReceive('getAttribute')->with('detail')->once()->andReturnNull();

        $pickup = Mockery::mock(Pickup::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn(OrderDelivery::TYPE_PICKUP);
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
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'order uuid');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($orderStatus = 'pending');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date = Carbon::now());
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->withArgs(['working_on_it'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($status);
        $resource = new BaseResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'            => $uuid,
            'status'        => $orderStatus,
            'status_detail' => null,
            'name'          => null,
            'supplier'      => new SupplierResource($supplier),
            'total_items'   => $itemOrdersCount,
            'channel'       => $channel,
            'created_at'    => $date,
            'working_on_it' => null,
            'delivery'      => new OrderDeliveryResource($orderDelivery),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
