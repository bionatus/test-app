<?php

namespace Tests\Unit\Http\Resources\Api\V2\Post\Comment;

use App\Http\Resources\Api\V2\Post\Comment\TaggedUserCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaggedUserCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $users = User::factory()->count(2)->create();

        $resource = new TaggedUserCollection($users);
        $response = $resource->resolve();

        $data = [
            'data' => UserResource::collection($users),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(TaggedUserCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
