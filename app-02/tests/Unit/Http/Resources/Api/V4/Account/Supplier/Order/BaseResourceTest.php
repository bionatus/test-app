<?php

namespace Tests\Unit\Http\Resources\Api\V4\Account\Supplier\Order;

use App\Http\Resources\Api\V4\Account\Supplier\Order\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\StaffResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Staff;
use App\Models\Status;
use App\Models\Substatus;
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
        $date    = Carbon::now();
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withAnyArgs()->once()->andReturn($itemOrdersCount = 77);

        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('detail')->once()->andReturn('fake status detail');
        $orderSubstatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date);

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);

        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);

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

        $orderStaff = Mockery::mock(OrderStaff::class);
        $staff      = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn('Fake name');
        $orderStaff->shouldReceive('getAttribute')->with('staff')->once()->andReturn($staff);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'order uuid');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'a name');
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date);
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->withArgs(['working_on_it'])->once()->andReturn('jonh doe');
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('totalPointsEarned')->with()->once()->andReturn($pointEarned = 1);
        $order->shouldReceive('getAttribute')->with('lastOrderStaff')->once()->andReturn($orderStaff);

        $resource = new BaseResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'               => $uuid,
            'current_status'   => new OrderSubstatusResource($orderSubstatus),
            'name'             => $name,
            'total_line_items' => $itemOrdersCount,
            'created_at'       => $date,
            'working_on_it'    => new StaffResource($staff),
            'delivery'         => new OrderDeliveryResource($orderDelivery),
            'points_earned'    => $pointEarned,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withAnyArgs()->once()->andReturn($itemOrdersCount = 0);

        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('detail')->once()->andReturnNull();
        $orderSubstatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn(Carbon::now());

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);

        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);

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
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date = Carbon::now());
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->withArgs(['working_on_it'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('totalPointsEarned')->with()->once()->andReturn($pointEarned = 1);
        $order->shouldReceive('getAttribute')->with('lastOrderStaff')->once()->andReturnNull();

        $resource = new BaseResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'               => $uuid,
            'current_status'   => new OrderSubstatusResource($orderSubstatus),
            'name'             => null,
            'total_line_items' => $itemOrdersCount,
            'created_at'       => $date,
            'working_on_it'    => null,
            'delivery'         => new OrderDeliveryResource($orderDelivery),
            'points_earned'    => $pointEarned,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
