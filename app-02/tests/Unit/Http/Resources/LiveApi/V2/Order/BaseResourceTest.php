<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V2\Order\BaseResource;
use App\Http\Resources\LiveApi\V2\Order\ItemResource;
use App\Http\Resources\LiveApi\V2\Order\StaffResource;
use App\Http\Resources\LiveApi\V2\Order\UserResource;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\SupplierUserResource;
use App\Http\Resources\Models\UserDeletedResource;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderLockedData;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Staff;
use App\Models\Status;
use App\Models\Substatus;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
    public function it_has_correct_fields_with_null_values()
    {
        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('detail')->once()->andReturn('fake status detail');
        $orderSubstatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn(Carbon::now());

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);

        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);

        $orderLockedData = Mockery::mock(OrderLockedData::class);
        $orderLockedData->shouldReceive('getAttribute')->with('user_first_name')->once()->andReturn('first name');
        $orderLockedData->shouldReceive('getAttribute')->with('user_last_name')->once()->andReturn('last name');
        $orderLockedData->shouldReceive('getAttribute')->with('user_company')->once()->andReturn('company name');
        $orderLockedData->shouldReceive('getAttribute')->with('user_public_name')->once()->andReturn('public name');

        $items = Collection::make();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('supplierUsers')->once()->andReturnNull();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('total')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('paid_total')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = 'create at');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('orderLockedData')->once()->andReturn($orderLockedData);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('getAttribute')->with('extra_items_added_later')->once()->andReturnFalse();
        $order->shouldReceive('getAttribute')->with('total_line_items')->once()->andReturn($totalLineItems = 1);
        $order->shouldReceive('getAttribute')->with('items')->once()->andReturn(Collection::make());
        $order->shouldReceive('getAttribute')->with('lastOrderStaff')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('company')->once()->andReturnNull();

        $resource = new BaseResource($order);
        $response = $resource->resolve();

        $data = [
            'id'               => $uuid,
            'name'             => null,
            'working_on_it'    => null,
            'created_at'       => $createdAt,
            'delivery'         => null,
            'had_truck_stock'  => false,
            'total_line_items' => $totalLineItems,
            'bid_number'       => null,
            'total'            => null,
            'paid_total'       => null,
            'current_status'   => new OrderSubstatusResource($orderSubstatus),
            'user'             => new UserDeletedResource($orderLockedData),
            'supplier_user'    => null,
            'items'            => ItemResource::collection($items),
            'company'          => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $orderStaff = Mockery::mock(OrderStaff::class);
        $staff      = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('1');
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn('Fake name');
        $orderStaff->shouldReceive('getAttribute')->with('staff')->once()->andReturn($staff);

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
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn('note fake');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn(190.23);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('pending');
        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $substatus->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('pending-requested');
        $orderSubstatus = Mockery::mock(OrderSubstatus::class);
        $orderSubstatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);
        $orderSubstatus->shouldReceive('getAttribute')->with('detail')->once()->andReturn('fake detail');
        $orderSubstatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn(Carbon::now());

        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->with('customer_tier')->once()->andReturn('customer tier');
        $supplierUser->shouldReceive('getAttribute')->with('status')->once()->andReturn(SupplierUser::STATUS_CONFIRMED);
        $supplierUser->shouldReceive('getAttribute')->with('cash_buyer')->once()->andReturnFalse();

        $supplierUsers = Mockery::mock(HasMany::class);
        $supplierUsers->shouldReceive('first')->withNoArgs()->once()->andReturn($supplierUser);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('devices')->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn('public name');
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn('company name');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('getAttribute')->with('supplierUsers')->once()->andReturn($supplierUsers);

        $items = Collection::make();

        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->with('city')->once()->andReturn('company city');
        $company->shouldReceive('getAttribute')->with('country')->once()->andReturn('company country');
        $company->shouldReceive('getAttribute')->with('name')->once()->andReturn('company state');
        $company->shouldReceive('getAttribute')->with('type')->once()->andReturn('company type');
        $company->shouldReceive('getAttribute')->with('state')->once()->andReturn('company state');
        $company->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('company zip_code');
        $company->shouldReceive('getAttribute')->with('address')->once()->andReturn('company address');
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('uuid');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturn($bidNumber = 'bid number');
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = 'create at');
        $order->shouldReceive('getAttribute')->with('total')->once()->andReturn($total = 100.25);
        $order->shouldReceive('getAttribute')->with('paid_total')->once()->andReturn($paidTotal = 100.25);
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturn('working on it');
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($orderSubstatus);
        $order->shouldReceive('getAttribute')->with('extra_items_added_later')->once()->andReturnTrue();
        $order->shouldReceive('getAttribute')->with('total_line_items')->once()->andReturn($totalLineItems = 1);
        $order->shouldReceive('getAttribute')->with('items')->once()->andReturn(Collection::make());
        $order->shouldReceive('getAttribute')->with('lastOrderStaff')->once()->andReturn($orderStaff);
        $order->shouldReceive('getAttribute')->with('company')->once()->andReturn($company);

        $resource = new BaseResource($order);
        $response = $resource->resolve();

        $data = [
            'id'               => $uuid,
            'name'             => $name,
            'working_on_it'    => new StaffResource($staff),
            'created_at'       => $createdAt,
            'delivery'         => new OrderDeliveryResource($orderDelivery),
            'had_truck_stock'  => true,
            'total_line_items' => $totalLineItems,
            'bid_number'       => $bidNumber,
            'total'            => $total,
            'paid_total'       => $paidTotal,
            'current_status'   => new OrderSubstatusResource($orderSubstatus),
            'user'             => new UserResource($user),
            'supplier_user'    => new SupplierUserResource($supplierUser),
            'items'            => ItemResource::collection($items),
            'company'          => new CompanyResource($company),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
