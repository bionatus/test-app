<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\User\Order\BaseResource;
use App\Http\Resources\LiveApi\V1\User\Order\OemResource;
use App\Http\Resources\LiveApi\V1\User\Order\UserResource;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\Series;
use App\Models\User;
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
        $order      = Mockery::mock(Order::class);
        $user       = Mockery::mock(User::class);
        $pickup     = Mockery::mock(Pickup::class);
        $delivery   = Mockery::mock(OrderDelivery::class);
        $itemOrders = Mockery::mock(HasMany::class);

        $itemOrders->shouldReceive('sum')->with('quantity')->once()->andReturn($totalItems = 10);

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

        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(55);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last name');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('public name');
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn('company');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-7554');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($created_at = Carbon::now());
        $order->shouldReceive('getAttribute')->withArgs(['oem'])->once()->andReturn(null);
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($subtotal = 12.0);
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturn($bid_number = 'bid number');
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($delivery);
        $order->shouldReceive('getAttribute')->with('discount')->once()->andReturn($discount = 5.25);
        $order->shouldReceive('getAttribute')->with('tax')->once()->andReturn($tax = 1.15);
        $order->shouldReceive('getAttribute')->with('itemOrders')->once()->andReturn($itemOrders);

        $resource = new BaseResource($order);

        $response = $resource->resolve();

        $data = [
            'id'            => $id,
            'name'          => null,
            'working_on_it' => null,
            'created_at'    => $created_at,
            'oem'           => null,
            'status'        => $status,
            'total'         => $subtotal,
            'user'          => new UserResource($user),
            'bid_number'    => $bid_number,
            'delivery'      => new OrderDeliveryResource($delivery),
            'discount'      => $discount,
            'tax'           => $tax,
            'total_items'   => $totalItems,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $order      = Mockery::mock(Order::class);
        $oem        = Mockery::mock(Oem::class);
        $series     = Mockery::mock(Series::class);
        $brand      = Mockery::mock(Brand::class);
        $user       = Mockery::mock(User::class);
        $pickup     = Mockery::mock(Pickup::class);
        $delivery   = Mockery::mock(OrderDelivery::class);
        $itemOrders = Mockery::mock(HasMany::class);

        $itemOrders->shouldReceive('sum')->with('quantity')->once()->andReturn($totalItems = 10);

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
        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now()->addDay());
        $delivery->shouldReceive('getAttribute')->with('start_time')->once()->andReturn(Carbon::createFromTime(15));
        $delivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturn(Carbon::createFromTime(18));
        $delivery->shouldReceive('getAttribute')->with('note')->once()->andReturn('fake note');
        $delivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn(12.22);
        $delivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $delivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $delivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(55);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last name');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('public name');
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn('company');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $brand->shouldReceive('getAttribute')->with('logo')->once()->andReturn([]);
        $brand->shouldIgnoreMissing('offsetExists');

        $series->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(22);
        $series->shouldIgnoreMissing('offsetExists');

        $oem->shouldReceive('getAttribute')->with('series')->once()->andReturn($series);
        $oem->shouldIgnoreMissing('offsetExists');

        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-7554');
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($created_at = Carbon::now());
        $order->shouldReceive('getAttribute')->with('oem')->twice()->andReturn($oem);
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturn($staffName = 'staff name');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'order name');
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('subTotal')->withNoArgs()->once()->andReturn($subtotal = 12);
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturn($bid_number = 'bid number');
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($delivery);
        $order->shouldReceive('getAttribute')->with('discount')->once()->andReturn($discount = 5.25);
        $order->shouldReceive('getAttribute')->with('tax')->once()->andReturn($tax = 1.15);
        $order->shouldReceive('getAttribute')->with('itemOrders')->once()->andReturn($itemOrders);

        $resource = new BaseResource($order);

        $response = $resource->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'working_on_it' => $staffName,
            'created_at'    => $created_at,
            'oem'           => new OemResource($oem),
            'status'        => $status,
            'total'         => $subtotal,
            'user'          => new UserResource($user),
            'bid_number'    => $bid_number,
            'delivery'      => new OrderDeliveryResource($delivery),
            'discount'      => $discount,
            'tax'           => $tax,
            'total_items'   => $totalItems,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
