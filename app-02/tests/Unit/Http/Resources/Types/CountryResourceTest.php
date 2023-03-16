<?php

namespace Tests\Unit\Http\Resources\Types;

use App\Http\Resources\Types\CountryResource;
use MenaraSolutions\Geographer\Earth;
use Tests\TestCase;

class CountryResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $geo     = new Earth();
        $country = $geo->getCountries()->useShortNames()->first();

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
