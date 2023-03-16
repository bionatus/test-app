<?php

namespace Tests\Unit\Http\Resources\Api\V3\Address\Country\State;

use App\Http\Resources\Api\V3\Address\Country\State\BaseResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MenaraSolutions\Geographer\State;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $state = State::build(3833578);

        $resource = new BaseResource($state);
        $response = $resource->resolve();

        $data = [
            'code' => $state->isoCode,
            'name' => $state->getName(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
