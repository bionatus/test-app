<?php

namespace Tests\Unit\Models;

use App\Models\ModelType;
use App\Models\Post;
use App\Models\Product;
use App\Models\Series;
use App\Models\Tag;

class ProductTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Product::tableName(), [
            'id',
            'model',
            'brand',
            'fields',
            'created_at',
            'updated_at',
            'series_id',
        ]);
    }

    /** @test */
    public function it_counts_posts()
    {
        $modelTypes = ModelType::factory()->count(2)->create();
        $modelType  = $modelTypes->first();
        $series     = Series::factory()->create();
        $product    = Product::factory()->usingSeries($series)->create();

        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $post3 = Post::factory()->create();
        Tag::factory()->usingPost($post1)->usingSeries($series)->create();
        Tag::factory()->usingPost($post2)->usingModelType($modelType)->create();
        Tag::factory()->usingPost($post3)->usingSeries($series)->create();
        Tag::factory()->usingPost($post3)->usingModelType($modelTypes->last())->create();

        $this->assertSame($product->postsCount(), 2);
    }
}
