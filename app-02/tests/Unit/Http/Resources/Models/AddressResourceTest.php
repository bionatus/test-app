<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AddressResource;
use App\Models\Address;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class AddressResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(AddressResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $address = Mockery::mock(Address::class);
        $address->shouldReceive('getAttribute')->withArgs(['address_1'])->once()->andReturn($address1 = 'fake a1');
        $address->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2 = 'fake a2');
        $address->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'fake city');
        $address->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state = 'fake state');
        $address->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country = 'fake country');
        $address->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = 'fake zipcode');
        $address->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturn($latitude = '12.23231');
        $address->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturn($longitude = '90.2165456');

        $resource = new AddressResource($address);

        $response = $resource->resolve();

        $data = [
            'address_1' => $address1,
            'address_2' => $address2,
            'city'      => $city,
            'state'     => $state,
            'country'   => $country,
            'zip_code'  => $zipCode,
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AddressResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_data()
    {
        $address1 = $this->faker->text(25);

        $address = Mockery::mock(Address::class);
        $address->shouldReceive('getAttribute')->withArgs(['address_1'])->once()->andReturn($address1);
        $address->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $address->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();

        $resource = new AddressResource($address);

        $response = $resource->resolve();

        $data = [
            'address_1' => $address1,
            'address_2' => null,
            'city'      => null,
            'state'     => null,
            'country'   => null,
            'zip_code'  => null,
            'latitude'  => null,
            'longitude' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AddressResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
