<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\User\Order\OrderDeliveryResource;
use App\Http\Resources\Models\CurriDeliveryResource;
use App\Http\Resources\Models\OtherDeliveryResource;
use App\Http\Resources\Models\PickupResource;
use App\Http\Resources\Models\WarehouseDeliveryResource;
use App\Http\Resources\Models\ShipmentDeliveryResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\OtherDelivery;
use App\Models\WarehouseDelivery;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class OrderDeliveryResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(OrderDeliveryResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_pickup()
    {
        $pickup = Mockery::mock(Pickup::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'pickup');
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_date')
            ->once()
            ->andReturn($requestedDate = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn($requestedStartTime = Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn($requestedEndTime = Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturn($date = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('start_time')
            ->once()
            ->andReturn($startTime = Carbon::createFromTime(15));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('end_time')
            ->once()
            ->andReturn($endTime = Carbon::createFromTime(18));
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'note');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn($fee = 123);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnTrue();

        $resource = new OrderDeliveryResource($orderDelivery);
        $response = $resource->resolve();

        $data = [
            'type'                 => $type,
            'requested_date'       => $requestedDate->format('Y-m-d'),
            'requested_start_time' => $requestedStartTime->format('H:i'),
            'requested_end_time'   => $requestedEndTime->format('H:i'),
            'date'                 => $date->format('Y-m-d'),
            'start_time'           => $startTime->format('H:i'),
            'end_time'             => $endTime->format('H:i'),
            'note'                 => $note,
            'fee'                  => $fee,
            'is_needed_now'        => false,
            'info'                 => null,
        ];

        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(OrderDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_curri_delivery()
    {
        $addressOrigin = Mockery::mock(Address::class);
        $addressOrigin->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('address 1');
        $addressOrigin->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $addressOrigin->shouldReceive('getAttribute')->with('city')->once()->andReturn('city');
        $addressOrigin->shouldReceive('getAttribute')->with('state')->once()->andReturn('state');
        $addressOrigin->shouldReceive('getAttribute')->with('country')->once()->andReturn('country');
        $addressOrigin->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('122345');
        $addressOrigin->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $addressOrigin->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();

        $addressDestination = Mockery::mock(Address::class);
        $addressDestination->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('address destination');
        $addressDestination->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $addressDestination->shouldReceive('getAttribute')->with('city')->once()->andReturn('city destination');
        $addressDestination->shouldReceive('getAttribute')->with('state')->once()->andReturn('state destination');
        $addressDestination->shouldReceive('getAttribute')->with('country')->once()->andReturn('country destination');
        $addressDestination->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('9874561');
        $addressDestination->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $addressDestination->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('quote_id')->once()->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('book_id')->once()->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_url')->once()->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('vehicle_type')->once()->andReturn('car');
        $curriDelivery->shouldReceive('getAttribute')->with('status')->once()->andReturn('status lorem');
        $curriDelivery->shouldReceive('getAttribute')->with('originAddress')->once()->andReturn($addressOrigin);
        $curriDelivery->shouldReceive('getAttribute')
            ->with('destinationAddress')
            ->once()
            ->andReturn($addressDestination);
        $curriDelivery->shouldReceive('getAttribute')
            ->with('supplier_confirmed_at')
            ->andReturn('supplier confirmation');

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'curri_delivery');
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_date')
            ->once()
            ->andReturn($requestedDate = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn($requestedStartTime = Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn($requestedEndTime = Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturn($date = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('start_time')
            ->once()
            ->andReturn($startTime = Carbon::createFromTime(15));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('end_time')
            ->once()
            ->andReturn($endTime = Carbon::createFromTime(18));
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'note');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn($fee = 123);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($curriDelivery);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnFalse();

        $resource = new OrderDeliveryResource($orderDelivery);
        $response = $resource->resolve();

        $data = [
            'type'                 => $type,
            'requested_date'       => $requestedDate->format('Y-m-d'),
            'requested_start_time' => $requestedStartTime->format('H:i'),
            'requested_end_time'   => $requestedEndTime->format('H:i'),
            'date'                 => $date->format('Y-m-d'),
            'start_time'           => $startTime->format('H:i'),
            'end_time'             => $endTime->format('H:i'),
            'note'                 => $note,
            'fee'                  => $fee,
            'is_needed_now'        => false,
            'info'                 => new CurriDeliveryResource($curriDelivery),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_warehouse_delivery()
    {
        $address = Mockery::mock(Address::class);
        $address->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('address 1');
        $address->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->with('city')->once()->andReturn('city');
        $address->shouldReceive('getAttribute')->with('state')->once()->andReturn('state');
        $address->shouldReceive('getAttribute')->with('country')->once()->andReturn('country');
        $address->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('122345');
        $address->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();

        $warehouseDelivery = Mockery::mock(WarehouseDelivery::class);
        $warehouseDelivery->shouldReceive('getAttribute')->with('destinationAddress')->once()->andReturn($address);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'warehouse_delivery');
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_date')
            ->once()
            ->andReturn($requestedDate = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn($requestedStartTime = Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn($requestedEndTime = Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturn($date = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('start_time')
            ->once()
            ->andReturn($startTime = Carbon::createFromTime(15));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('end_time')
            ->once()
            ->andReturn($endTime = Carbon::createFromTime(18));
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'note');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn($fee = 123);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($warehouseDelivery);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnFalse();

        $resource = new OrderDeliveryResource($orderDelivery);
        $response = $resource->resolve();

        $data = [
            'type'                 => $type,
            'requested_date'       => $requestedDate->format('Y-m-d'),
            'requested_start_time' => $requestedStartTime->format('H:i'),
            'requested_end_time'   => $requestedEndTime->format('H:i'),
            'date'                 => $date->format('Y-m-d'),
            'start_time'           => $startTime->format('H:i'),
            'end_time'             => $endTime->format('H:i'),
            'note'                 => $note,
            'fee'                  => $fee,
            'is_needed_now'        => false,
            'info'                 => new WarehouseDeliveryResource($warehouseDelivery),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_other_delivery()
    {
        $address = Mockery::mock(Address::class);
        $address->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('address 1');
        $address->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->with('city')->once()->andReturn('city');
        $address->shouldReceive('getAttribute')->with('state')->once()->andReturn('state');
        $address->shouldReceive('getAttribute')->with('country')->once()->andReturn('country');
        $address->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('122345');
        $address->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();

        $otherDelivery = Mockery::mock(OtherDelivery::class);
        $otherDelivery->shouldReceive('getAttribute')->with('destinationAddress')->once()->andReturn($address);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'other_delivery');
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_date')
            ->once()
            ->andReturn($requestedDate = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn($requestedStartTime = Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn($requestedEndTime = Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturn($date = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('start_time')
            ->once()
            ->andReturn($startTime = Carbon::createFromTime(15));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('end_time')
            ->once()
            ->andReturn($endTime = Carbon::createFromTime(18));
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'note');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn($fee = 123);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($otherDelivery);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnFalse();

        $resource = new OrderDeliveryResource($orderDelivery);
        $response = $resource->resolve();

        $data = [
            'type'                 => $type,
            'requested_date'       => $requestedDate->format('Y-m-d'),
            'requested_start_time' => $requestedStartTime->format('H:i'),
            'requested_end_time'   => $requestedEndTime->format('H:i'),
            'date'                 => $date->format('Y-m-d'),
            'start_time'           => $startTime->format('H:i'),
            'end_time'             => $endTime->format('H:i'),
            'note'                 => $note,
            'fee'                  => $fee,
            'is_needed_now'        => false,
            'info'                 => new OtherDeliveryResource($otherDelivery),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_shipment_delivery()
    {
        $address = Mockery::mock(Address::class);
        $address->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('address 1');
        $address->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->with('city')->once()->andReturn('city');
        $address->shouldReceive('getAttribute')->with('state')->once()->andReturn('state');
        $address->shouldReceive('getAttribute')->with('country')->once()->andReturn('country');
        $address->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('122345');
        $address->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();

        $shipmentDelivery = Mockery::mock(ShipmentDelivery::class);
        $shipmentDelivery->shouldReceive('getAttribute')->with('destinationAddress')->once()->andReturn($address);
        $shipmentDelivery->shouldReceive('getAttribute')->with('shipmentDeliveryPreference')->once()->andReturnNull();

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'shipping_delivery');
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_date')
            ->once()
            ->andReturn($requestedDate = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn($requestedStartTime = Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn($requestedEndTime = Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturn($date = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('start_time')
            ->once()
            ->andReturn($startTime = Carbon::createFromTime(15));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('end_time')
            ->once()
            ->andReturn($endTime = Carbon::createFromTime(18));
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'note');
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturn($fee = 123);
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($shipmentDelivery);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->with()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->with()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('isPickup')->with()->once()->andReturnFalse();

        $resource = new OrderDeliveryResource($orderDelivery);
        $response = $resource->resolve();

        $data = [
            'type'                 => $type,
            'requested_date'       => $requestedDate->format('Y-m-d'),
            'requested_start_time' => $requestedStartTime->format('H:i'),
            'requested_end_time'   => $requestedEndTime->format('H:i'),
            'date'                 => $date->format('Y-m-d'),
            'start_time'           => $startTime->format('H:i'),
            'end_time'             => $endTime->format('H:i'),
            'note'                 => $note,
            'fee'                  => $fee,
            'is_needed_now'        => false,
            'info'                 => new ShipmentDeliveryResource($shipmentDelivery),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $pickup = Mockery::mock(Pickup::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'pickup');
        $orderDelivery->shouldReceive('getAttribute')->with('requested_date')->once()->andReturn($date = Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn($requestedStartTime = Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn($requestedEndTime = Carbon::createFromTime(12));
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

        $resource = new OrderDeliveryResource($orderDelivery);
        $response = $resource->resolve();

        $data = [
            'type'                 => $type,
            'requested_date'       => $date->format('Y-m-d'),
            'requested_start_time' => $requestedStartTime->format('H:i'),
            'requested_end_time'   => $requestedEndTime->format('H:i'),
            'date'                 => null,
            'start_time'           => null,
            'end_time'             => null,
            'note'                 => null,
            'fee'                  => null,
            'is_needed_now'        => false,
            'info'                 => null,
        ];

        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(OrderDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
