<?php

namespace Tests\Unit\Http\Resources\Api\V3\Supplier;

use App\Http\Resources\Api\V3\Supplier\BaseResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\ForbiddenZipCode;
use App\Models\Supplier;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
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
            ->andReturn($address = 'Adress lorem ipsum');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address_2'])
            ->once()
            ->andReturn($addressTwo = 'Second Adress lorem ipsum');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'City lorem ipsum');
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->once()
            ->andReturn(CountryDataType::UNITED_STATES . '-AR');
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = '90210');
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturn($latitude = '90');
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturn($longitude = '90');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['published_at'])
            ->once()
            ->andReturn($publishedAt = new Carbon());
        $supplier->shouldReceive('getAttribute')->withArgs(['preferred_supplier'])->once()->andReturnFalse();
        $supplier->shouldReceive('getAttribute')->withArgs(['distance'])->once()->andReturn($distance = 0.26);
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnTrue();


        $resource = new BaseResource($supplier);

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
            'published'              => !!$publishedAt,
            'preferred'              => false,
            'distance'               => $distance,
            'can_use_curri_delivery' => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @dataProvider isCurriDeliveryEnabledProvider
     */
    public function it_has_correct_fields_with_null_values(
        bool $expected
    )
    {
        $id   = '1234-56789';
        $name = 'A supplier';

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['preferred_supplier'])->once()->andReturnFalse();
        $supplier->shouldReceive('getAttribute')->withArgs(['distance'])->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturn($expected);

        $resource = new BaseResource($supplier);

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
            'distance'               => null,
            'can_use_curri_delivery' => $expected,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    public function isCurriDeliveryEnabledProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
