<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AddressResource;
use App\Http\Resources\Models\ShipmentDeliveryPreferenceResource;
use App\Http\Resources\Models\ShipmentDeliveryResource;
use App\Models\Address;
use App\Models\ShipmentDelivery;
use App\Models\ShipmentDeliveryPreference;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class ShipmentDeliveryResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(ShipmentDeliveryResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $destinationAddress = Mockery::mock(Address::class);
        $destinationAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('destination address');
        $destinationAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $destinationAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $destinationAddress->shouldReceive('getAttribute')->with('country')->once()->andReturn('fake country');
        $destinationAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zipcode');
        $destinationAddress->shouldReceive('getAttribute')->with('latitude')->once()->andReturn('12.23231');
        $destinationAddress->shouldReceive('getAttribute')->with('longitude')->once()->andReturn('90.2165456');

        $preference = Mockery::mock(ShipmentDeliveryPreference::class);
        $preference->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('slug');

        $shipmentDelivery = Mockery::mock(ShipmentDelivery::class);
        $shipmentDelivery->shouldReceive('getAttribute')->with('destinationAddress')->andReturn($destinationAddress);
        $shipmentDelivery->shouldReceive('getAttribute')->with('shipmentDeliveryPreference')->andReturn($preference);

        $resource = new ShipmentDeliveryResource($shipmentDelivery);

        $response = $resource->resolve();

        $data = [
            'destination_address'          => new AddressResource($destinationAddress),
            'shipment_delivery_preference' => new ShipmentDeliveryPreferenceResource($preference),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ShipmentDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $shipmentDelivery = Mockery::mock(ShipmentDelivery::class);
        $shipmentDelivery->shouldReceive('getAttribute')->with('destinationAddress')->andReturnNull();
        $shipmentDelivery->shouldReceive('getAttribute')->with('shipmentDeliveryPreference')->andReturnNull();

        $resource = new ShipmentDeliveryResource($shipmentDelivery);

        $response = $resource->resolve();

        $data = [
            'destination_address'          => null,
            'shipment_delivery_preference' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ShipmentDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
