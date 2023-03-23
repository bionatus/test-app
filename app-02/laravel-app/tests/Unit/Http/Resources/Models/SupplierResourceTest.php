<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\SupplierResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Supplier;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Tests\TestCase;

class SupplierResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
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
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new SupplierResource($supplier);

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
            'can_use_curri_delivery' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_country()
    {
        $id   = '1234-56789';
        $name = 'A supplier';

        $country         = Country::build(CountryDataType::UNITED_STATES);
        $countryResource = new CountryResource($country);
        $country->getStates();

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new SupplierResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => null,
            'address_2'              => null,
            'city'                   => null,
            'state'                  => null,
            'country'                => $countryResource,
            'zip_code'               => null,
            'latitude'               => null,
            'longitude'              => null,
            'published'              => false,
            'can_use_curri_delivery' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_country_and_state()
    {
        $id   = '1234-56789';
        $name = 'A supplier';

        $country         = Country::build(CountryDataType::UNITED_STATES);
        $countryResource = new CountryResource($country);

        $states        = $country->getStates();
        $state         = $states->filter(fn(State $state) => $country->isoCode . '-AR' === $state->isoCode)->first();
        $stateResource = new StateResource($state);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->once()
            ->andReturn(CountryDataType::UNITED_STATES . '-AR');
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new SupplierResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => null,
            'address_2'              => null,
            'city'                   => null,
            'state'                  => $stateResource,
            'country'                => $countryResource,
            'zip_code'               => null,
            'latitude'               => null,
            'longitude'              => null,
            'published'              => false,
            'can_use_curri_delivery' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @dataProvider isCurriDeliveryEnabledProvider
     */
    public function it_get_can_use_curri_delivery_depending_is_curri_delivery_enabled(
        bool $expected
    ) {
        $id   = '1234-56789';
        $name = 'A supplier';

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn('12345');
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturn($expected);

        $resource = new SupplierResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => null,
            'address_2'              => null,
            'city'                   => null,
            'state'                  => null,
            'country'                => null,
            'zip_code'               => '12345',
            'latitude'               => null,
            'longitude'              => null,
            'published'              => false,
            'can_use_curri_delivery' => $expected,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
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
