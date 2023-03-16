<?php

namespace Tests\Unit\Http\Resources\Api\V3\Store;

use App\Http\Resources\Api\V3\Store\StateResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MenaraSolutions\Geographer\State;
use Tests\TestCase;

class StateResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $state = State::build(3833578);

        $resource = new StateResource($state);
        $response = $resource->resolve();

        $data = [
            'code' => $state->isoCode,
            'name' => $state->getName(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(StateResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
