<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Post\Scopes\TaggableTypesQuantity;
use App\Models\Series;
use App\Models\Tag;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TaggableTypesQuantityTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws Exception
     */
    public function it_orders_by_tags_count()
    {
        $post1     = Post::factory()->create();
        $post2     = Post::factory()->create();
        $post3     = Post::factory()->create();
        $series    = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        $plainTag  = PlainTag::factory()->create();

        Tag::factory()->usingPost($post2)->usingPlainTag($plainTag)->create();
        Tag::factory()->usingPost($post2)->usingSeries($series)->create();
        Tag::factory()->usingPost($post2)->usingModelType($modelType)->create();

        Tag::factory()->usingPost($post3)->usingPlainTag($plainTag)->create();
        Tag::factory()->usingPost($post3)->usingSeries($series)->create();

        Tag::factory()->usingPost($post1)->usingModelType($modelType)->create();

        $collection = Collection::make([$plainTag->toTagType(), $series->toTagType(), $modelType->toTagType()]);

        $posts           = Post::scoped(new TaggableTypesQuantity($collection))->get();
        $expectedIndexes = Collection::make([$post2->getKey(), $post3->getKey(), $post1->getKey()]);

        $this->assertEquals($expectedIndexes, $posts->pluck(Post::keyName()));
    }
}
