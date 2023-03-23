<?php

namespace Tests\Unit\Http\Resources\Api\V2\Activity;

use App\Http\Resources\Api\V2\Activity\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Request;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $user = User::factory()->create();

        $resource = new UserResource($user);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'         => $user->getRouteKey(),
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
