<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Post\BaseResource;
use App\Http\Resources\Api\V2\Tag\PostCollection;
use App\Models\PlainTag;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use stdClass;
use Tests\TestCase;

class PostCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $plainTag = PlainTag::factory()->issue()->create();
        Tag::factory()->usingPlainTag($plainTag)->count(10)->create();

        $page = $plainTag->posts()->with(['media'])->withCount(['comments'])->latest()->latest('id')->paginate();

        $resource = new PostCollection($page);
        $response = $resource->resolve();

        $data = [
            'data'           => BaseResource::collection($page),
            'has_more_pages' => $page->hasMorePages(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PostCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_returns_latest_posts_first()
    {
        $plainTag = PlainTag::factory()->issue()->create();
        Tag::factory()->usingPlainTag($plainTag)->count(50)->create();

        $page  = $plainTag->posts()->latest()->latest('id')->paginate();
        $posts = Collection::make($page->items());

        $resource = new PostCollection($page);

        $data = Collection::make($resource->response()->getData()->data);

        $data->each(function(stdClass $displayedPost, int $index) use ($posts) {
            $this->assertEquals($posts->get($index)->getRouteKey(), $displayedPost->id);
        });
    }
}
