<?php

namespace Tests\Unit\Http\Resources\Api\V3\Activity;

use App\Http\Resources\Api\V3\Activity\CompanyResource;
use App\Models\Company;
use Carbon\Carbon;
use Request;
use Tests\TestCase;

class CompanyResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $company = \Mockery::mock(Company::class);
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '123');
        $company->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'fake company');
        $company->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'fake type');
        $company->shouldReceive('getAttribute')->with('country')->once()->andReturn($country = 'USA');
        $company->shouldReceive('getAttribute')->with('state')->once()->andReturn($state = 'New York');
        $company->shouldReceive('getAttribute')->with('city')->once()->andReturn($city = 'New York');
        $company->shouldReceive('getAttribute')->with('address')->once()->andReturn($address = 'address fake');
        $company->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn($zipCode = '001234');
        $company->shouldReceive('getAttribute')->with('latitude')->once()->andReturn($latitude = '13,2465');
        $company->shouldReceive('getAttribute')->with('longitude')->once()->andReturn($longitude = '89,4512');
        $company->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $company->shouldReceive('getAttribute')->with('updated_at')->once()->andReturn($updatedAt = $createdAt);

        $resource = new CompanyResource($company);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'         => $id,
            'name'       => $name,
            'type'       => $type,
            'country'    => $country,
            'state'      => $state,
            'city'       => $city,
            'address'    => $address,
            'zip_code'   => $zipCode,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CompanyResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null()
    {
        $company = \Mockery::mock(Company::class);
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '123');
        $company->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('type')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('address')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $company->shouldReceive('getAttribute')->with('updated_at')->once()->andReturn($updatedAt = $createdAt);

        $resource = new CompanyResource($company);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'         => $id,
            'name'       => null,
            'type'       => null,
            'country'    => null,
            'state'      => null,
            'city'       => null,
            'address'    => null,
            'zip_code'   => null,
            'latitude'   => null,
            'longitude'  => null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CompanyResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
