<?php

namespace Tests\Unit\Http\Resources\Api\V4\Order;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Http\Resources\Api\V4\Order\SupplierResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Status;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $uuid        = $this->faker->uuid;
        $name        = $this->faker->text(50);
        $createdAt   = $this->faker->date('Y-m-d H:i:s');
        $countryType = CountryDataType::UNITED_STATES;
        $country     = Country::build($countryType);
        $states      = $country->getStates();
        $state       = $states->filter(fn(State $state) => $state->isoCode === $countryType . '-AR')->first();

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('supplier name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturn('supplier address');
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('supplier address_2');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn('supplier city');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturn($country->getCode());
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturn('phone');
        $supplier->shouldReceive('getAttribute')->with('state')->once()->andReturn($state->isoCode);
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('12345');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

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
        $orderDelivery->shouldReceive('isWarehouseDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->withNoArgs()->once()->andReturnTrue();

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
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid);
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt);
        $order->shouldReceive('getAttribute')->with('supplier')->twice()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('total')->once()->andReturn($total = 100.25);
        $order->shouldReceive('getAttribute')->with('paid_total')->once()->andReturn($paidTotal = 100.25);
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturn($workingOnIt = 'jonh doe');
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $company = Mockery::mock(Company::class);
        $order->shouldReceive('getAttribute')->with('company')->once()->andReturn($company);

        $resource = new BaseResource($order);
        $response = $resource->resolve();

        $data = [
            'id'              => $uuid,
            'name'            => $name,
            'delivery'        => new OrderDeliveryResource($orderDelivery),
            'working_on_it'   => $workingOnIt,
            'supplier'        => new SupplierResource($order->supplier),
            'total'           => $total,
            'paid_total'      => $paidTotal,
            'total_items'     => $itemOrdersCount,
            'current_status'  => new OrderSubstatusResource($orderSubstatus),
            'channel'         => $channel,
            'created_at'      => $createdAt,
            'company_account' => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $uuid      = $this->faker->uuid;
        $createdAt = $this->faker->date('Y-m-d H:i:s');

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('supplier name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturn('supplier address');
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('supplier address_2');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn('supplier city');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturn('supplier contact phone');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $action = Mockery::mock(GetChannelByOrder::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($channel = 'channel');
        App::bind(GetChannelByOrder::class, fn() => $action);

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($itemOrdersCount = 0);

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
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid);
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('total')->once()->andReturn($total = 100.25);
        $order->shouldReceive('getAttribute')->with('paid_total')->once()->andReturn($paidTotal = 100.25);
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('getAttribute')->with('company')->once()->andReturnNull();

        $resource = new BaseResource($order);
        $response = $resource->resolve();

        $data = [
            'id'              => $uuid,
            'name'            => null,
            'delivery'        => null,
            'working_on_it'   => null,
            'supplier'        => new SupplierResource($supplier),
            'total'           => $total,
            'paid_total'      => $paidTotal,
            'total_items'     => $itemOrdersCount,
            'current_status'  => new OrderSubstatusResource($orderSubstatus),
            'channel'         => $channel,
            'created_at'      => $createdAt,
            'company_account' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
