<?php

namespace Tests\Unit\Http\Resources\Api\V2\Agent;

use App\Http\Resources\Api\V2\Agent\BaseResource;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $agent = Agent::factory()->create();

        $resource = new BaseResource($agent);

        $response = $resource->resolve();

        $data = [
            'id'   => $agent->getRouteKey(),
            'user' => new UserResource($agent->user),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
