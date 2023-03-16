<?php

namespace Tests\Unit\Models\Note\Scopes;

use App\Models\Note;
use App\Models\Note\Scopes\AlphabeticallyWithNullLast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AlphabeticallyWithNullLastTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sorts_by_sort_field_with_null_last()
    {
        $scopeField   = 'sort';
        $fourthSupply = Note::factory()->create();
        $secondSupply = Note::factory()->create(['sort' => 2]);
        $thirdSupply  = Note::factory()->create(['sort' => 3]);
        $firstSupply  = Note::factory()->create(['sort' => 1]);

        $expectedNote = Collection::make([
            $firstSupply,
            $secondSupply,
            $thirdSupply,
            $fourthSupply,
        ]);

        $orderedSupplies = Note::scoped(new AlphabeticallyWithNullLast($scopeField))->get();

        $orderedSupplies->each(function(Note $note) use ($expectedNote) {
            $this->assertSame($expectedNote->shift()->getKey(), $note->getKey());
        });
    }
}
