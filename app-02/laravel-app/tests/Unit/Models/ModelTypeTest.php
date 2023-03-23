<?php

namespace Tests\Unit\Models;

use App\Models\IsTaggable;
use App\Models\Media;
use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Tag;
use App\Types\TaggableType;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTypeTest extends ModelTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ModelType::tableName(), [
            'id',
            'slug',
            'sort',
            'name',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_its_oems_count()
    {
        $packageModelType = ModelType::factory()->create([
            'name' => 'Package',
        ]);

        $condenserModelType = ModelType::factory()->create([
            'name' => 'Condenser',
        ]);

        Oem::factory()->count(3)->usingModelType($packageModelType)->create();

        Oem::factory()->count(2)->usingModelType($condenserModelType)->create();

        $this->assertEquals(3, $packageModelType->oemsCount());
        $this->assertEquals(2, $condenserModelType->oemsCount());
    }

    /** @test */
    public function it_uses_slug()
    {
        $modelType = ModelType::factory()->create(['slug' => 'something']);

        $this->assertEquals($modelType->slug, $modelType->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $modelType = ModelType::factory()->make(['slug' => null]);
        $modelType->save();

        $this->assertNotNull($modelType->slug);
    }

    /** @test */
    public function it_is_a_taggable()
    {
        $taggable = new ModelType();

        $this->assertInstanceOf(IsTaggable::class, $taggable);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_raw_tag_representation()
    {
        $modelType = ModelType::factory()->create();

        $rawModelType = $modelType->toTagType();
        $this->assertInstanceOf(TaggableType::class, $rawModelType);

        $this->assertEquals($modelType->getRouteKey(), $rawModelType->id);
        $this->assertEquals($modelType::MORPH_ALIAS, $rawModelType->type);
        $this->assertEquals($modelType->name, $rawModelType->name);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_the_media_if_required()
    {
        $modelType = ModelType::factory()->create();
        $media     = Media::factory()->usingTag($modelType)->create();

        $rawTag = $modelType->toTagType(true);
        $this->assertInstanceOf(TaggableType::class, $rawTag);

        $this->assertEquals($modelType->getRouteKey(), $rawTag->id);
        $this->assertSame($modelType->name, $rawTag->name);
        $this->assertSame($media->uuid, $rawTag->getMedia()[0]->uuid);
    }

    /** @test
     * @throws Exception
     */
    public function it_ignores_with_media_when_returning_a_raw_tag_representation()
    {
        $modelType = ModelType::factory()->create();

        $rawModelType = $modelType->toTagType();
        $this->assertInstanceOf(TaggableType::class, $rawModelType);

        $this->assertEquals([], $rawModelType->getMedia());
    }

    /** @test */
    public function it_returns_a_taggable_route_key()
    {
        $modelType = ModelType::factory()->create();

        $this->assertEquals(Tag::TYPE_MODEL_TYPE . '-' . $modelType->getRouteKey(), $modelType->taggableRouteKey());
    }
}
