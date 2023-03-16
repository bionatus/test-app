<?php

namespace Tests\Unit\Http\Resources\Api\V3\Supplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\Supplier\DetailedResource;
use App\Http\Resources\Api\V3\Supplier\SupplierHourResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use RefreshDatabase;

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
        $supplier->shouldReceive('getAttribute')->with('contact_email')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('distance')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('supplierHours')->twice()->andReturn(Collection::make());
        $supplier->shouldReceive('getAttribute')->withArgs(['contact_phone'])->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();

        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $resource = new DetailedResource($supplier);

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
            'logo'                   => null,
            'image'                  => null,
            'email'                  => null,
            'phone'                  => null,
            'distance'               => null,
            'open_hours'             => SupplierHourResource::collection([]),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('getAttribute')->with('uuid')->twice()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturn(false);

        $country         = Country::build(CountryDataType::UNITED_STATES);
        $countryResource = new CountryResource($country);

        $states        = $country->getStates();
        $state         = $states->filter(fn(State $state) => $country->isoCode . '-AR' === $state->isoCode)->first();
        $stateResource = new StateResource($state);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->withArgs(['timezone'])->twice()->andReturn('Europe/London');

        $supplierHour = Mockery::mock(SupplierHour::class);
        $supplierHour->shouldReceive('getAttribute')->with('day')->twice()->andReturn('monday');
        $supplierHour->shouldReceive('getAttribute')->with('from')->once()->andReturn('09:30 am');
        $supplierHour->shouldReceive('getAttribute')->with('to')->once()->andReturn('05:30 pm');
        $supplierHour->shouldReceive('getAttribute')->withArgs(['supplier'])->twice()->andReturn($supplier);
        $supplierHour->shouldReceive('setRelation')->withArgs(['supplier', null])->once();

        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')
            ->with('address')
            ->once()
            ->andReturn($address = 'An address Lorem ipsum');
        $supplier->shouldReceive('getAttribute')
            ->with('address_2')
            ->once()
            ->andReturn($addressTwo = 'Another address Lorem ipsum');
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturn($city = 'Lorem City');
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->once()
            ->andReturn(CountryDataType::UNITED_STATES . '-AR');
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn($zipCode = '12345');
        $supplier->shouldReceive('getAttribute')->with('latitude')->once()->andReturn($latitude = '-17.4159593');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['longitude'])
            ->once()
            ->andReturn($longitude = '-66.1603339,17');
        $supplier->shouldReceive('getAttribute')->with('published_at')->once()->andReturn(new Carbon());
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::LOGO)->once()->andReturn($media);
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);
        $supplier->shouldReceive('getAttribute')
            ->with('contact_email')
            ->once()
            ->andReturn($contactEmail = 'email@email.com');
        $supplier->shouldReceive('getAttribute')->withArgs(['distance'])->once()->andReturn($distance = 0.26);
        $supplier->shouldReceive('getAttribute')
            ->with('contact_phone')
            ->once()
            ->andReturn($contactPhone = '+591 8754564');
        $supplier->shouldReceive('getAttribute')
            ->with('supplierHours')
            ->twice()
            ->andReturn(Collection::make([$supplierHour]));
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnTrue();
        $supplier->shouldReceive('withoutRelations')->withNoArgs()->once();

        $resource = new DetailedResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => $address,
            'address_2'              => $addressTwo,
            'city'                   => $city,
            'state'                  => $stateResource,
            'country'                => $countryResource,
            'zip_code'               => $zipCode,
            'latitude'               => $latitude,
            'longitude'              => $longitude,
            'published'              => true,
            'can_use_curri_delivery' => true,
            'logo'                   => new ImageResource($media),
            'image'                  => new ImageResource($media),
            'email'                  => $contactEmail,
            'phone'                  => $contactPhone,
            'distance'               => $distance,
            'open_hours'             => SupplierHourResource::collection([$supplierHour]),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @dataProvider isCurriDeliveryEnabledProvider
     */
    public function it_get_can_use_curri_delivery_depending_is_curri_delivery_enabled(
        bool $expected
    ) {

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn('11111');
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('contact_email')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('distance')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('supplierHours')->twice()->andReturn(Collection::make());
        $supplier->shouldReceive('getAttribute')->withArgs(['contact_phone'])->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturn($expected);

        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();

        $resource = new DetailedResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => null,
            'address_2'              => null,
            'city'                   => null,
            'state'                  => null,
            'country'                => null,
            'zip_code'               => '11111',
            'latitude'               => null,
            'longitude'              => null,
            'published'              => false,
            'image'                  => null,
            'email'                  => null,
            'distance'               => null,
            'open_hours'             => SupplierHourResource::collection([]),
            'phone'                  => null,
            'logo'                   => null,
            'can_use_curri_delivery' => $expected,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
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
