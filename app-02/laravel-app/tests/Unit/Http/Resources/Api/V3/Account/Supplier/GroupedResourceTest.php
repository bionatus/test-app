<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Supplier;

use App\Http\Resources\Api\V3\Account\Supplier\GroupedResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Supplier;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Tests\TestCase;

class GroupedResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->twice()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturn(false);

        $country         = Country::build(CountryDataType::UNITED_STATES);
        $countryResource = new CountryResource($country);
        $country->getStates();

        $states        = $country->getStates();
        $state         = $states->filter(fn(State $state) => $country->isoCode . '-AR' === $state->isoCode)->first();
        $stateResource = new StateResource($state);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address'])
            ->once()
            ->andReturn($address = 'Address lorem ipsum');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address_2'])
            ->once()
            ->andReturn($addressTwo = 'Second Address lorem ipsum');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'City lorem ipsum');
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->once()
            ->andReturn(CountryDataType::UNITED_STATES . '-AR');
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = '90210');
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturn($latitude = '90');
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturn($longitude = '90');
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturn(new Carbon());
        $supplier->shouldReceive('getAttribute')->withArgs(['preferred_supplier'])->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturn($media);
        $supplier->shouldReceive('getAttribute')->withArgs(['distance'])->once()->andReturn($distance = 0.26);
        $supplier->shouldReceive('getAttribute')->withArgs(['favorite'])->once()->andReturnTrue();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnTrue();

        $resource = new GroupedResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => $address,
            'address_2'              => $addressTwo,
            'city'                   => $city,
            'country'                => $countryResource,
            'state'                  => $stateResource,
            'zip_code'               => $zipCode,
            'latitude'               => $latitude,
            'longitude'              => $longitude,
            'published'              => true,
            'preferred'              => false,
            'can_use_curri_delivery' => true,
            'logo'                   => new ImageResource($media),
            'image'                  => new ImageResource($media),
            'distance'               => $distance,
            'favorite'               => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(GroupedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['preferred_supplier'])->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['distance'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['favorite'])->once()->andReturnFalse();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new GroupedResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
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
            'preferred'              => false,
            'can_use_curri_delivery' => false,
            'logo'                   => null,
            'image'                  => null,
            'distance'               => null,
            'favorite'               => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(GroupedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
