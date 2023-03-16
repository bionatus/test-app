<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order\Unprocessed;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\UserResource;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
        $devices = Mockery::mock(Collection::class);
        $devices->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['devices'])->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn('first name');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn('last name');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('public name');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['oldestPendingOrder'])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn('company name');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

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
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['bid_number'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($createdAt = 'create at');
        $order->shouldReceive('getAttribute')->withArgs(['discount'])->once()->andReturn($discount = 12.34);
        $order->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->withArgs(['orderDelivery'])->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->withArgs(['tax'])->once()->andReturn($tax = 56.78);
        $order->shouldReceive('getAttribute')->withArgs(['user'])->twice()->andReturn($user);
        $order->shouldReceive('getAttribute')->withArgs(['working_on_it'])->once()->andReturnNull();
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($total = 100.25);

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $resource = new BaseResource($order);
        $response = $resource->resolve();

        $data = [
            'id'            => $uuid,
            'name'          => null,
            'working_on_it' => null,
            'created_at'    => $createdAt,
            'status'        => $status,
            'total'         => $total,
            'user'          => new UserResource($user),
            'bid_number'    => null,
            'delivery'      => new OrderDeliveryResource($orderDelivery),
            'discount'      => $discount,
            'tax'           => $tax,
            'channel'       => $channel,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $devices = Mockery::mock(Collection::class);
        $devices->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

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
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('start_time')
            ->once()
            ->andReturn(Carbon::createFromTime(15));
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturn(Carbon::createFromTime(18));
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn('fake note');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn(15);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $oldestPendingOrder = Mockery::mock(Order::class);
        $oldestPendingOrder->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn(Carbon::now());

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['devices'])->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn('first name');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn('last name');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('public name');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['oldestPendingOrder'])->once()->andReturn($oldestPendingOrder);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn('company name');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['bid_number'])->once()->andReturn($bidNumber = 'bid number');
        $order->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($createdAt = 'create at');
        $order->shouldReceive('getAttribute')->withArgs(['discount'])->once()->andReturn($discount = 12.34);
        $order->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $order->shouldReceive('getAttribute')->withArgs(['orderDelivery'])->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->withArgs(['tax'])->once()->andReturn($tax = 56.78);
        $order->shouldReceive('getAttribute')->withArgs(['user'])->times(2)->andReturn($user);
        $order->shouldReceive('getAttribute')
            ->withArgs(['working_on_it'])
            ->once()
            ->andReturn($workingOnIt = 'working on it');
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($total = 100.25);

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $resource = new BaseResource($order);
        $response = $resource->resolve();

        $data = [
            'id'            => $uuid,
            'name'          => $name,
            'working_on_it' => $workingOnIt,
            'created_at'    => $createdAt,
            'status'        => $status,
            'total'         => $total,
            'user'          => new UserResource($user),
            'bid_number'    => $bidNumber,
            'delivery'      => new OrderDeliveryResource($orderDelivery),
            'discount'      => $discount,
            'tax'           => $tax,
            'channel'       => $channel,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
