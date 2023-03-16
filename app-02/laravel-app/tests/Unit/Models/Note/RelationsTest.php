<?php

namespace Tests\Unit\Models\Note;

use App\Models\NoteCategory;
use App\Models\Note;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Note $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Note::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_note_category()
    {
        $related = $this->instance->noteCategory()->first();

        $this->assertInstanceOf(NoteCategory::class, $related);
    }
}
