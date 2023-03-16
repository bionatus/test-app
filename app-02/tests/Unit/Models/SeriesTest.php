<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\IsTaggable;
use App\Models\Series;
use App\Models\Tag;
use App\Types\TaggableType;
use Exception;

class SeriesTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Series::tableName(), [
            'id',
            'brand_id',
            'uuid',
            'name',
            'image',
            'published_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_is_a_taggable()
    {
        $taggable = new Series();

        $this->assertInstanceOf(IsTaggable::class, $taggable);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_raw_tag_representation()
    {
        $series = Series::factory()->create();

        $rawSeries = $series->toTagType();
        $this->assertInstanceOf(TaggableType::class, $rawSeries);

        $this->assertEquals($series->getRouteKey(), $rawSeries->id);
        $this->assertSame($series::MORPH_ALIAS, $rawSeries->type);
        $this->assertSame($series->compositeName(), $rawSeries->name);
    }

    /** @test
     * @throws Exception
     */
    public function it_ignores_with_media_when_returning_a_raw_tag_representation()
    {
        $series = Series::factory()->create();

        $rawSeries = $series->toTagType(true);
        $this->assertInstanceOf(TaggableType::class, $rawSeries);

        $this->assertEquals($series->getRouteKey(), $rawSeries->id);
        $this->assertSame($series::MORPH_ALIAS, $rawSeries->type);
        $this->assertSame($series->compositeName(), $rawSeries->name);
        $this->assertEquals([], $rawSeries->getMedia());
    }

    /** @test */
    public function it_returns_a_taggable_route_key()
    {
        $series = Series::factory()->create();

        $this->assertEquals(Tag::TYPE_SERIES . '-' . $series->getRouteKey(), $series->taggableRouteKey());
    }

    /** @test */
    public function it_returns_a_composite_name()
    {
        $brandName  = 'brandName';
        $brand      = Brand::factory()->create(['name' => $brandName]);
        $seriesName = 'seriesName';
        $series     = Series::factory()->usingBrand($brand)->create(['name' => $seriesName]);

        $this->assertSame($brandName . "|" . $seriesName, $series->compositeName());
    }

    /** @test */
    public function it_returns_only_the_series_name_as_composite_name_when_series_has_no_brand()
    {
        $seriesName = 'seriesName';
        $series     = Series::factory()->create(['brand_id' => null, 'name' => $seriesName]);

        $this->assertSame("|" . $seriesName, $series->compositeName());
    }

    /** @test */
    public function it_returns_only_the_brand_name_as_composite_name_when_series_has_no_name()
    {
        $brandName = 'brandName';
        $brand     = Brand::factory()->create(['name' => $brandName]);
        $series    = Series::factory()->usingBrand($brand)->create(['name' => null]);

        $this->assertSame($brandName . "|", $series->compositeName());
    }

    /** @test */
    public function it_returns_pipe_as_composite_name_when_series_has_no_name_nor_related_brand()
    {
        $series = Series::factory()->create(['brand_id' => null, 'name' => null]);

        $this->assertSame("|", $series->compositeName());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $post = Series::factory()->make(['uuid' => null]);
        $post->save();

        $this->assertNotNull($post->uuid);
    }
}
