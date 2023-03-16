<?php

namespace Tests\Unit\Http\Resources\Api\V3\Store;

use App\Http\Resources\Api\V3\Store\CountryResource;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MenaraSolutions\Geographer\Country;
use Tests\TestCase;

class CountryResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $country = Country::build(CountryDataType::UNITED_STATES);

        $resource = new CountryResource($country);
        $response = $resource->resolve();

        $data = [
            'code' => $country->getCode(),
            'name' => $country->getName(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CountryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
