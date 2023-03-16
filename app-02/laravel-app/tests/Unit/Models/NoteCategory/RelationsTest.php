<?php

namespace Tests\Unit\Models\NoteCategory;

use App\Models\NoteCategory;
use App\Models\Note;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property NoteCategory $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = NoteCategory::factory()->create();
    }

    /** @test */
    public function it_has_notes()
    {
        Note::factory()->usingNoteCategory($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->notes()->get();

        $this->assertCorrectRelation($related, Note::class);
    }
}
