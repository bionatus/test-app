<?php

namespace Tests\Unit\Http\Resources\Api\Nova\Address\Country\State;

use App\Http\Resources\Api\Nova\Address\Country\State\StateResource;
use MenaraSolutions\Geographer\State;
use Tests\TestCase;

class StateResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $state = State::build(3833578);

        $resource = new StateResource($state);
        $response = $resource->resolve();

        $data = [
            'value'   => $state->getIsoCode(),
            'display' => $state->getName(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(StateResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
