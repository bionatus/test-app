<?php

namespace Tests\Unit\Http\Resources\Api\V4\Order;

use App;
use App\Actions\Models\Order\CalculatePoints;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\Api\V4\Order\DetailedResource;
use App\Http\Resources\Api\V4\Order\ItemResource;
use App\Http\Resources\Api\V4\Order\SupplierResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Order\InvoiceResource;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\StaffResource;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Staff;
use App\Models\Status;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Types\Point;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(DetailedResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $items = Collection::make();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->with('quantity')->once()->andReturn($itemOrdersCount = 0);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn($totalLineItems = 0);
        $hasMany->shouldReceive('take')->with(3)->once()->andReturnSelf();
        $hasMany->shouldReceive('with')->withAnyArgs()->once()->andReturnSelf();
        $hasMany->shouldReceive('get')->withNoArgs()->once()->andReturnSelf();
        $hasMany->shouldReceive('pluck')->with('item')->once()->andReturn($items);

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

        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('pending');

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $substatus->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('pending-requested');

        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);
        $orderSubstatus->shouldReceive('getAttribute')->with('detail')->once()->andReturn('fake detail');
        $orderSubstatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn(Carbon::now());

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');

        $orderStaff = Mockery::mock(OrderStaff::class);
        $staff      = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn('Fake name');
        $orderStaff->shouldReceive('getAttribute')->with('staff')->once()->andReturn($staff);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'order uuid');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'Lorem Ipsum Name');
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturn($bidNumber = '86546546');
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('total')->once()->andReturn($total = 100);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'Fake note');
        $order->shouldReceive('getFirstMedia')->with('invoice')->once()->andReturn($media);
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturn('john doe');
        $order->shouldReceive('getAttribute')->with('lastOrderStaff')->once()->andReturn($orderStaff);

        $pointData = Mockery::mock(Point::class);
        $pointData->shouldReceive('points')->withNoArgs()->once()->andReturn($points = 10);

        $calculatePoints = Mockery::mock(CalculatePoints::class);
        $calculatePoints->shouldReceive('execute')->withNoArgs()->once()->andReturn($pointData);
        App::bind(CalculatePoints::class, fn() => $calculatePoints);

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $resource = new DetailedResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'                   => $id,
            'current_status'       => new OrderSubstatusResource($orderSubstatus),
            'name'                 => $name,
            'total'                => $total,
            'supplier'             => new SupplierResource($supplier),
            'total_items_quantity' => $itemOrdersCount,
            'total_line_items'     => $totalLineItems,
            'channel'              => $channel,
            'items'                => ItemResource::collection($items),
            'bid_number'           => $bidNumber,
            'delivery'             => new OrderDeliveryResource($orderDelivery),
            'created_at'           => $createdAt,
            'working_on_it'        => new StaffResource($staff),
            'points'               => $points,
            'note'                 => $note,
            'invoice'              => new InvoiceResource($media),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->with('quantity')->once()->andReturn($itemOrdersCount = 0);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn($totalLineItems = 0);
        $hasMany->shouldReceive('take')->with(3)->once()->andReturnSelf();
        $hasMany->shouldReceive('with')->withAnyArgs()->once()->andReturnSelf();
        $hasMany->shouldReceive('get')->withNoArgs()->once()->andReturnSelf();
        $hasMany->shouldReceive('pluck')->with('item')->once()->andReturn($items = Collection::make());

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

        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('pending');

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $substatus->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('pending-requested');

        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);
        $orderSubstatus->shouldReceive('getAttribute')->with('detail')->once()->andReturn('fake detail');
        $orderSubstatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn(Carbon::now());

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'order uuid');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'Lorem Name');
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $order->shouldReceive('getAttribute')->with('total')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'Fake note');
        $order->shouldReceive('getFirstMedia')->with('invoice')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('lastOrderStaff')->once()->andReturnNull();

        $pointData = Mockery::mock(Point::class);
        $pointData->shouldReceive('points')->withNoArgs()->once()->andReturn($points = 10);

        $calculatePoints = Mockery::mock(CalculatePoints::class);
        $calculatePoints->shouldReceive('execute')->withNoArgs()->once()->andReturn($pointData);
        App::bind(CalculatePoints::class, fn() => $calculatePoints);

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $resource = new DetailedResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'                   => $id,
            'current_status'       => new OrderSubstatusResource($orderSubstatus),
            'name'                 => $name,
            'total'                => null,
            'supplier'             => new SupplierResource($supplier),
            'total_items_quantity' => $itemOrdersCount,
            'total_line_items'     => $totalLineItems,
            'channel'              => $channel,
            'items'                => ItemResource::collection($items),
            'bid_number'           => null,
            'delivery'             => new OrderDeliveryResource($orderDelivery),
            'created_at'           => $createdAt,
            'working_on_it'        => null,
            'points'               => $points,
            'note'                 => $note,
            'invoice'              => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
