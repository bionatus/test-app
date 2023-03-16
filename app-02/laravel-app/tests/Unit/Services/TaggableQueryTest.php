<?php

namespace Tests\Unit\Services;

use App\Models\Media;
use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Series;
use App\Scopes\Alphabetically;
use App\Scopes\ByType;
use App\Services\TaggableQuery;
use DB;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TaggableQueryTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws \ReflectionException
     */
    public function it_applies_alphabetically_scope()
    {
        $taggableQuery = new TaggableQuery('series');
        $queryProperty = new \ReflectionProperty(get_class($taggableQuery), 'query');

        $otherQuery = DB::query();
        $scoped     = new Alphabetically();
        $scoped->apply($otherQuery);

        $this->assertEquals($queryProperty->getValue($taggableQuery)->orders, $otherQuery->orders);
    }

    /** @test */
    public function it_applies_by_type_scope()
    {
        $taggableQuery = new TaggableQuery('series');
        $queryProperty = new \ReflectionProperty(get_class($taggableQuery), 'query');

        $otherQuery = DB::query();
        $scoped     = new ByType('series');
        $scoped->apply($otherQuery);

        $this->assertEquals($queryProperty->getValue($taggableQuery)->wheres, $otherQuery->wheres);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_tag_type_query_of_tags()
    {
        $series    = Series::factory()->create();
        $issue     = PlainTag::factory()->issue()->create();
        $modelType = ModelType::factory()->create();

        $taggableQuery = new TaggableQuery();

        $returnedData = $taggableQuery->query()->get();

        $returnedSeries = $returnedData->first(function($returnedObject) use ($series) {
            $seriesTagType = $series->toTagType();

            return $returnedObject->id == $seriesTagType->id && $returnedObject->type === $seriesTagType->type && $returnedObject->name === $seriesTagType->name;
        });
        $this->assertNotNull($returnedSeries);

        $returnedIssue = $returnedData->first(function($returnedObject) use ($issue) {
            $issueTagType = $issue->toTagType();

            return $returnedObject->id === $issueTagType->id && $returnedObject->type === $issueTagType->type && $returnedObject->name === $issueTagType->name;
        });
        $this->assertNotNull($returnedIssue);

        $returnedModelType = $returnedData->first(function($returnedObject) use ($modelType) {
            $modelTypeTagType = $modelType->toTagType();

            return $returnedObject->id === $modelTypeTagType->id && $returnedObject->type === $modelTypeTagType->type && $returnedObject->name === $modelTypeTagType->name;
        });
        $this->assertNotNull($returnedModelType);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_paginated_tag_type_collection()
    {
        $series    = Series::factory()->create();
        $issue     = PlainTag::factory()->issue()->create();
        $modelType = ModelType::factory()->create();

        $taggableQuery = new TaggableQuery();

        $paginated = $taggableQuery->paginate(10);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);

        $this->assertCount(3, $paginated);

        $expected = new Collection();
        $expected->push($series->toTagType());
        $expected->push($issue->toTagType());
        $expected->push($modelType->toTagType());

        $this->assertEqualsCanonicalizing($expected->toArray(), $paginated->items());
    }

    /** @test */
    public function it_returns_the_taggable_type_with_his_media_if_available()
    {
        $issue = PlainTag::factory()->issue()->create();
        $media = Media::factory()->usingTag($issue)->create();

        $taggableQuery = new TaggableQuery();

        $paginated = $taggableQuery->paginate(10);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);

        $this->assertCount(1, $paginated);

        $returnedTag = $paginated->items()[0];

        $returnedMedias = $returnedTag->getMedia();
        $this->assertIsArray($returnedMedias);
        $this->assertCount(1, $returnedMedias);
        $this->assertInstanceOf(Media::class, $returnedMedias[0]);
        $this->assertEquals($media->uuid, $returnedMedias[0]->uuid);
    }
}
