<?php

namespace Tests\Unit\Http\Resources\Api\V2\Post;

use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Http\Resources\Api\V2\Post\CommentCollection;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use stdClass;
use Tests\TestCase;

class CommentCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $post = Post::factory()->create();
        $page = $post->comments()->paginate();

        $resource = new CommentCollection($page);
        $response = $resource->resolve();

        $data = [
            'data'           => BaseResource::collection($page),
            'has_more_pages' => $page->hasMorePages(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CommentCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_returns_oldest_comments_first()
    {
        $post     = Post::factory()->create();
        $comments = Comment::factory()->usingPost($post)->count(100)->create();

        $resource = new CommentCollection($post->comments()->paginate());

        $data = Collection::make($resource->response()->getData()->data);

        $data->each(function(stdClass $displayedComment, int $index) use ($comments) {
            $this->assertEquals($comments->get($index)->getRouteKey(), $displayedComment->id);
        });
    }
}
