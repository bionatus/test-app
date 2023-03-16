<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AddressResource;
use App\Http\Resources\Models\WarehouseDeliveryResource;
use App\Models\Address;
use App\Models\WarehouseDelivery;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class WarehouseDeliveryResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(WarehouseDeliveryResource::class);

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

        $warehouseDelivery = Mockery::mock(WarehouseDelivery::class);
        $warehouseDelivery->shouldReceive('getAttribute')->with('destinationAddress')->andReturn($destinationAddress);

        $resource = new WarehouseDeliveryResource($warehouseDelivery);

        $response = $resource->resolve();

        $data = [
            'destination_address' => new AddressResource($destinationAddress),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WarehouseDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $warehouseDelivery = Mockery::mock(WarehouseDelivery::class);
        $warehouseDelivery->shouldReceive('getAttribute')->with('destinationAddress')->andReturnNull();

        $resource = new WarehouseDeliveryResource($warehouseDelivery);

        $response = $resource->resolve();

        $data = [
            'destination_address' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WarehouseDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
