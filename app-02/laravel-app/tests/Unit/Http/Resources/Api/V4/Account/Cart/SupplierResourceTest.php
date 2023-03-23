<?php

namespace Tests\Unit\Http\Resources\Api\V4\Account\Cart;

use App\Http\Resources\Api\V4\Account\Cart\BaseResource;
use App\Http\Resources\Api\V4\Account\Cart\SupplierResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Supplier;
use App\Types\CountryDataType;
use Illuminate\Support\Carbon;
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
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();

        $resource = new SupplierResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'                     => $uuid,
            'name'                   => $name,
            'address'                => null,
            'address_2'              => null,
            'city'                   => null,
            'state'                  => null,
            'country'                => null,
            'zip_code'               => null,
            'latitude'               => null,
            'longitude'              => null,
            'published'              => false,
            'can_use_curri_delivery' => false,
            'logo'                   => null,
            'image'                  => null,
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
        $supplier->shouldReceive('getAttribute')->with('state')->once()->andReturn($state->isoCode);
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn($zipCode = '12345');
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturn($media);
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnTrue();
        $supplier->shouldReceive('getAttribute')->with('latitude')->once()->andReturn($latitude = "latitude");
        $supplier->shouldReceive('getAttribute')->with('longitude')->once()->andReturn($longitude = "longitude");
        $supplier->shouldReceive('getAttribute')->with('published_at')->once()->andReturn(new Carbon());

        $resource = new SupplierResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'                     => $uuid,
            'name'                   => $name,
            'address'                => $address,
            'address_2'              => $address2,
            'city'                   => $city,
            'state'                  => new StateResource($state),
            'country'                => new CountryResource($country),
            'zip_code'               => $zipCode,
            'latitude'               => $latitude,
            'longitude'              => $longitude,
            'published'              => true,
            'can_use_curri_delivery' => true,
            'logo'                   => new ImageResource($media),
            'image'                  => new ImageResource($media),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
