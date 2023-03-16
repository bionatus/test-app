<?php

namespace Tests\Unit\Http\Resources\Api\V3\Address\Country;

use App\Http\Resources\Api\V3\Address\Country\BaseResource;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MenaraSolutions\Geographer\Country;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $country = Country::build(CountryDataType::UNITED_STATES);

        $resource = new BaseResource($country);
        $response = $resource->resolve();

        $data = [
            'code' => $country->getCode(),
            'name' => $country->getName(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
