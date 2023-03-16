<?php

namespace Tests\Unit\Models;

use App\Models\GroupedReplacement;
use App\Models\PartNote;
use App\Models\Replacement;
use App\Models\ReplacementNote;
use App\Models\SingleReplacement;

class ReplacementTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Replacement::tableName(), [
            'id',
            'original_part_id',
            'uuid',
            'type',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_can_determine_if_a_replacement_is_a_single_replacement()
    {
        $single  = SingleReplacement::factory()->create()->replacement;
        $grouped = GroupedReplacement::factory()->create()->replacement;

        $this->assertTrue($single->isSingle());
        $this->assertFalse($grouped->isSingle());
    }

    /** @test */
    public function it_can_display_complete_notes_with_only_replacement_note_having_the_first_three_characters_removed()
    {
        $singleReplacement = SingleReplacement::factory()->create();
        $replacement       = $singleReplacement->replacement;
        ReplacementNote::factory()->usingReplacement($replacement)->create(['value' => 'XX A note']);

        $notes         = $replacement->completeNotes();
        $expectedNotes = 'A note';

        $this->assertEquals($expectedNotes, $notes);
    }

    /** @test */
    public function it_can_display_complete_notes_with_only_part_note()
    {
        $partNote          = PartNote::factory()->create();
        $part              = $partNote->part;
        $singleReplacement = SingleReplacement::factory()->usingPart($part)->create();

        $notes         = $singleReplacement->replacement->completeNotes();
        $expectedNotes = $partNote->value;

        $this->assertEquals($expectedNotes, $notes);
    }

    /** @test */
    public function it_can_display_complete_notes_with_replacement_note_and_part_note_concatenated()
    {
        $partNote          = PartNote::factory()->create(['value' => 'A part note']);
        $part              = $partNote->part;
        $singleReplacement = SingleReplacement::factory()->usingPart($part)->create();
        $replacement       = $singleReplacement->replacement;
        ReplacementNote::factory()->usingReplacement($replacement)->create(['value' => 'XX A replacement note']);

        $notes         = $replacement->completeNotes();
        $expectedNotes = 'A replacement note' . PHP_EOL . 'A part note';

        $this->assertEquals($expectedNotes, $notes);
    }
}
