<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AddressResource;
use App\Http\Resources\Models\CurriDeliveryResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CurriDeliveryResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(CurriDeliveryResource::class);

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

        $originAddress = Mockery::mock(Address::class);
        $originAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('origin address');
        $originAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('originAddress')->andReturn($originAddress);
        $curriDelivery->shouldReceive('getAttribute')->with('destinationAddress')->andReturn($destinationAddress);
        $curriDelivery->shouldReceive('getAttribute')->with('quote_id')->andReturn($quoteId = 'xefa_2342adfa');
        $curriDelivery->shouldReceive('getAttribute')->with('book_id')->andReturn($bookId = 'book id');
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_id')->andReturn('tracking_id');
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_url')->andReturn($trackingUrl = 'tracking_url');
        $curriDelivery->shouldReceive('getAttribute')->with('vehicle_type')->andReturn($vehicleType = 'vehicle type');
        $curriDelivery->shouldReceive('getAttribute')->with('status')->andReturn($status = 'status test');
        $curriDelivery->shouldReceive('getAttribute')
            ->with('supplier_confirmed_at')
            ->andReturn($supplierConfirmedAt = 'supplier confirmation');

        $resource = new CurriDeliveryResource($curriDelivery);

        $response = $resource->resolve();

        $data = [
            'supplier_confirmed_at' => $supplierConfirmedAt,
            'quote_id'              => $quoteId,
            'book_id'               => $bookId,
            'tracking_url'          => $trackingUrl,
            'vehicle_type'          => $vehicleType,
            'status'                => $status,
            'origin_address'        => new AddressResource($originAddress),
            'destination_address'   => new AddressResource($destinationAddress),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CurriDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_data()
    {
        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('originAddress')->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('destinationAddress')->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('quote_id')->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('book_id')->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_id')->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_url')->andReturnNull();
        $curriDelivery->shouldReceive('getAttribute')
            ->with('vehicle_type')
            ->andReturn($vehicleType = CurriDelivery::VEHICLE_TYPE_CAR);
        $curriDelivery->shouldReceive('getAttribute')->with('status')->andReturn($status = 'status test');
        $curriDelivery->shouldReceive('getAttribute')
            ->with('supplier_confirmed_at')
            ->andReturn($supplierConfirmedAt = 'supplier confirmation');

        $resource = new CurriDeliveryResource($curriDelivery);

        $response = $resource->resolve();

        $data = [
            'supplier_confirmed_at' => $supplierConfirmedAt,
            'quote_id'              => null,
            'book_id'               => null,
            'tracking_url'          => null,
            'vehicle_type'          => $vehicleType,
            'status'                => $status,
            'origin_address'        => null,
            'destination_address'   => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CurriDeliveryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
