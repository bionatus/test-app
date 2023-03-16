<?php

namespace Tests\Unit\Models;

use App\Constants\MediaCollectionNames;
use App\Models\Note;
use Illuminate\Support\Collection;
use ReflectionException;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Str;

class NoteTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Note::tableName(), [
            'id',
            'note_category_id',
            'slug',
            'title',
            'body',
            'link',
            'link_text',
            'sort',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_has_slug_trait()
    {
        $this->assertUseTrait(Note::class, HasSlug::class, ['getSlugOptions']);
    }

    /** @test */
    public function it_uses_slug_as_route_key()
    {
        $note = Note::factory()->create(['slug' => 'something']);

        $this->assertEquals($note->slug, $note->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $note = Note::factory()->make(['slug' => null]);
        $note->save();

        $this->assertNotNull($note->slug);
    }

    /** @test */
    public function it_generates_slug_from_title()
    {
        $note = Note::factory()->make(['slug' => null, 'title' => $title = 'is a new tittle']);
        $note->save();

        $this->assertEquals(Str::slug($title), $note->slug);
    }

    /** @test */
    public function it_does_not_changes_slug_on_update()
    {
        $note        = Note::factory()->create(['slug' => $slug = 'something']);
        $note->title = 'other tittle';
        $note->update();

        $this->assertEquals($slug, $note->slug);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_interacts_with_media_trait()
    {
        $this->assertUseTrait(Note::class, InteractsWithMedia::class, ['registerMediaCollections']);
    }

    /** @test */
    public function it_registers_media_collections()
    {
        $note = Note::factory()->create();
        $note->registerMediaCollections();

        $mediaCollectionNames = Collection::make($note->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::IMAGES, $mediaCollectionNames);
    }
}
