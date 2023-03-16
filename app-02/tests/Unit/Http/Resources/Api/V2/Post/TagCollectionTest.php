<?php

namespace Tests\Unit\Http\Resources\Api\V2\Post;

use App\Http\Resources\Api\V2\Post\TagCollection;
use App\Http\Resources\Api\V2\Tag\BaseResource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TagCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $tag           = Tag::factory()->create();
        $tagCollection = Collection::make([$tag]);

        $resource = new TagCollection($tagCollection);
        $response = $resource->resolve();

        $data = [
            'data' => BaseResource::collection([$tag->taggable->toTagType()]),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(TagCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
