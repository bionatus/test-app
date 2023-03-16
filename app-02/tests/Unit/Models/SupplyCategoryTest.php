<?php

namespace Tests\Unit\Models;

use App\Constants\MediaCollectionNames;
use App\Models\SupplyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;
use ReflectionException;

class SupplyCategoryTest extends ModelTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplyCategory::tableName(), [
            'id',
            'slug',
            'name',
            'sort',
            'visible_at',
            'parent_id',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_slug()
    {
        $supplyCategory = SupplyCategory::factory()->create(['slug' => 'something']);

        $this->assertEquals($supplyCategory->slug, $supplyCategory->getRouteKey());
    }

    /** @test */
    public function it_sets_visible_and_not_visible()
    {
        $visible    = SupplyCategory::factory()->visible()->make();
        $notVisible = SupplyCategory::factory()->make();

        $this->assertTrue($visible->isVisible());
        $this->assertFalse($notVisible->isVisible());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $supplyCategory = SupplyCategory::factory()->make(['slug' => null]);
        $supplyCategory->save();

        $this->assertNotNull($supplyCategory->slug);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_interacts_with_media_trait()
    {
        $this->assertUseTrait(SupplyCategory::class, InteractsWithMedia::class, ['registerMediaCollections']);
    }

    /** @test */
    public function it_registers_media_collections()
    {
        $supplyCategory = SupplyCategory::factory()->create();
        $supplyCategory->registerMediaCollections();

        $mediaCollectionNames = Collection::make($supplyCategory->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::IMAGES, $mediaCollectionNames);
    }
}
