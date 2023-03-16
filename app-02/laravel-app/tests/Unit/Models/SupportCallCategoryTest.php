<?php

namespace Tests\Unit\Models;

use App\Constants\MediaCollectionNames;
use App\Models\SupportCallCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use ReflectionException;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupportCallCategoryTest extends ModelTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupportCallCategory::tableName(), [
            'id',
            'parent_id',
            'slug',
            'name',
            'description',
            'phone',
            'sort',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_slug_as_route_key()
    {
        $supportCallCategory = SupportCallCategory::factory()->create(['slug' => 'something']);

        $this->assertEquals($supportCallCategory->slug, $supportCallCategory->getRouteKey());
    }

    /** @test */
    public function it_fills_the_slug_on_creation()
    {
        $supportCallCategory = SupportCallCategory::factory()->create(['slug' => $slug = 'slug']);
        $supportCallCategory->update(['name' => 'name']);

        $supportCallCategory = $supportCallCategory->fresh();

        $this->assertSame($slug, $supportCallCategory->slug);
    }

    /** @test */
    public function it_does_not_change_the_slug_on_update()
    {
        $supportCallCategory = SupportCallCategory::factory()->make(['slug' => null]);
        $supportCallCategory->save();

        $this->assertNotNull($supportCallCategory->slug);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_interacts_with_media_trait()
    {
        $this->assertUseTrait(SupportCallCategory::class, InteractsWithMedia::class, ['registerMediaCollections']);
    }

    /** @test */
    public function it_registers_media_collections()
    {
        $supportCallCategory = SupportCallCategory::factory()->create();
        $supportCallCategory->registerMediaCollections();

        $mediaCollectionNames = Collection::make($supportCallCategory->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::IMAGES, $mediaCollectionNames);
    }
}
