<?php

namespace Tests\Unit\Models;

use App\Models\NoteCategory;
use ReflectionException;
use Spatie\Sluggable\HasSlug;
use Str;

class NoteCategoryTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(NoteCategory::tableName(), [
            'id',
            'slug',
            'name',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_has_slug_trait()
    {
        $this->assertUseTrait(NoteCategory::class, HasSlug::class, ['getSlugOptions']);
    }

    /** @test */
    public function it_uses_slug_as_route_key()
    {
        $noteCategory = NoteCategory::factory()->create(['slug' => 'something']);

        $this->assertEquals($noteCategory->slug, $noteCategory->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $noteCategory = NoteCategory::factory()->make(['slug' => null]);
        $noteCategory->save();

        $this->assertNotNull($noteCategory->slug);
    }

    /** @test */
    public function it_generates_slug_from_name()
    {
        $noteCategory = NoteCategory::factory()->make(['slug' => null, 'name' => $name = 'is a new name']);
        $noteCategory->save();

        $this->assertEquals(Str::slug($name), $noteCategory->slug);
    }

    /** @test */
    public function it_does_not_changes_slug_on_update()
    {
        $noteCategory       = NoteCategory::factory()->create(['slug' => $slug = 'something']);
        $noteCategory->name = 'other name';
        $noteCategory->update();

        $this->assertEquals($slug, $noteCategory->slug);
    }
}
