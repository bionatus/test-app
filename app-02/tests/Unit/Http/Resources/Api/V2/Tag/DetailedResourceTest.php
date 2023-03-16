<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\DetailedResource;
use App\Http\Resources\Api\V2\Tag\ImagedResource;
use App\Http\Resources\Api\V2\Tag\PostCollection;
use App\Models\PlainTag;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTaggable;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @throws Exception
     */
    public function it_has_correct_fields()
    {
        $plainTag = PlainTag::factory()->issue()->create();

        $resource = new DetailedResource($plainTag->toTagType(), User::factory()->create());

        $response = $resource->resolve();

        $data = (new ImagedResource($plainTag->toTagType()))->toArrayWithAdditionalData([
            'following'       => false,
            'posts_count'     => 0,
            'followers_count' => 0,
            'posts'           => new PostCollection($plainTag->posts()->paginate()),
        ]);

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @throws Exception
     */
    public function it_shows_following_true_if_the_auth_user_follows_the_tag()
    {
        $plainTag     = PlainTag::factory()->issue()->create();
        $userTaggable = UserTaggable::factory()->usingPlainTag($plainTag)->create();

        $resource = new DetailedResource($plainTag->toTagType(), $userTaggable->user);

        $response = $resource->resolve();

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
        $this->assertTrue($response['following']);
    }

    /** @test
     * @throws Exception
     */
    public function it_shows_the_count_of_posts_that_contains_the_tag()
    {
        $plainTag = PlainTag::factory()->issue()->create();
        Tag::factory()->usingPlainTag($plainTag)->count(10)->create();

        $resource = new DetailedResource($plainTag->toTagType(), User::factory()->create());

        $response = $resource->resolve();

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
        $this->assertSame(10, $response['posts_count']);
    }

    /** @test
     * @throws Exception
     */
    public function it_shows_the_count_of_users_that_follow_the_tag()
    {
        $plainTag = PlainTag::factory()->issue()->create();
        UserTaggable::factory()->usingPlainTag($plainTag)->count(10)->create();

        $resource = new DetailedResource($plainTag->toTagType(), User::factory()->create());

        $response = $resource->resolve();

        $data = [
            'id'              => $plainTag->getRouteKey(),
            'type'            => $plainTag->type,
            'followers_count' => 10,
        ];

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
        $this->assertSame(10, $response['followers_count']);
    }
}
