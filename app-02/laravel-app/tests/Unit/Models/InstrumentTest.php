<?php

namespace Tests\Unit\Models;

use App\Constants\MediaCollectionNames;
use App\Models\Instrument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use ReflectionException;
use Spatie\MediaLibrary\InteractsWithMedia;

class InstrumentTest extends ModelTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Instrument::tableName(), [
            'id',
            'slug',
            'name',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_slug_as_route_key()
    {
        $instrument = Instrument::factory()->create(['slug' => 'something']);

        $this->assertEquals($instrument->slug, $instrument->getRouteKey());
    }

    /** @test */
    public function it_fills_the_slug_on_creation()
    {
        $instrument = Instrument::factory()->create(['slug' => $slug = 'slug']);
        $instrument->update(['name' => 'name']);

        $instrument = $instrument->fresh();

        $this->assertSame($slug, $instrument->slug);
    }

    /** @test */
    public function it_does_not_change_the_slug_on_update()
    {
        $instrument = Instrument::factory()->make(['slug' => null]);
        $instrument->save();

        $this->assertNotNull($instrument->slug);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_interacts_with_media_trait()
    {
        $this->assertUseTrait(Instrument::class, InteractsWithMedia::class, ['registerMediaCollections']);
    }

    /** @test */
    public function it_registers_media_collections()
    {
        $supportCallCategory = Instrument::factory()->create();
        $supportCallCategory->registerMediaCollections();

        $mediaCollectionNames = Collection::make($supportCallCategory->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::IMAGES, $mediaCollectionNames);
    }
}
