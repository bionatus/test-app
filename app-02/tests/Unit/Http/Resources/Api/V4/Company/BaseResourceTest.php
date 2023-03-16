<?php

namespace Tests\Unit\Http\Resources\Api\V4\Company;

use App\Http\Resources\Api\V4\Company\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Company;
use App\Types\CountryDataType;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(CompanyResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'city');
        $company->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $company->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'type');
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = 'zip_code');
        $company->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address = 'address');
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid');

        $response = (new BaseResource($company))->resolve();

        $data = [
            'id'       => $id,
            'name'     => $name,
            'type'     => $type,
            'country'  => null,
            'state'    => null,
            'city'     => $city,
            'address'  => $address,
            'zip_code' => $zipCode,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $countryType = CountryDataType::UNITED_STATES;
        $country     = Country::build($countryType);
        $states      = $country->getStates();
        $state       = $states->filter(fn(State $state) => $state->isoCode === $countryType . '-AR')->first();

        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'city');
        $company->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->getCode());
        $company->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $company->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state->isoCode);
        $company->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'type');
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = 'zip_code');
        $company->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address = 'address');
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid');

        $response = (new BaseResource($company))->resolve();

        $data = [
            'id'       => $id,
            'name'     => $name,
            'type'     => $type,
            'country'  => new CountryResource($country),
            'state'    => new StateResource($state),
            'city'     => $city,
            'address'  => $address,
            'zip_code' => $zipCode,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
