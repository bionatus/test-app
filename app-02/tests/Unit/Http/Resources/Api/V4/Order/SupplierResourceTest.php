<?php

namespace Tests\Unit\Http\Resources\Api\V4\Order;

use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Http\Resources\Api\V4\Order\SupplierResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Supplier;
use App\Types\CountryDataType;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SupplierResourceTest extends TestCase
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
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $resource = new SupplierResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'        => $uuid,
            'name'      => $name,
            'address'   => null,
            'address_2' => null,
            'city'      => null,
            'phone'     => null,
            'logo'      => null,
            'image'     => null,
            'state'     => null,
            'zip_code'  => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->twice()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturn(false);

        $countryType = CountryDataType::UNITED_STATES;
        $country     = Country::build($countryType);
        $states      = $country->getStates();
        $state       = $states->filter(fn(State $state) => $state->isoCode === $countryType . '-AR')->first();

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturn($address = 'address');
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturn($address2 = 'address_2');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn($city = 'city');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturn($country->getCode());
        $supplier->shouldReceive('getAttribute')->with('contact_phone')->once()->andReturn($phone = 'phone');
        $supplier->shouldReceive('getAttribute')->with('state')->once()->andReturn($state->isoCode);
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn($zipCode = '12345');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturn($media);

        $resource = new SupplierResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'        => $uuid,
            'name'      => $name,
            'address'   => $address,
            'address_2' => $address2,
            'city'      => $city,
            'phone'     => $phone,
            'logo'      => new ImageResource($media),
            'image'     => new ImageResource($media),
            'state'     => new StateResource($state),
            'zip_code'  => $zipCode,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
