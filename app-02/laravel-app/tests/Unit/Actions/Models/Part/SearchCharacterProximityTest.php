<?php

namespace Tests\Unit\Actions\Models\Part;

use App;
use App\Actions\Models\Part\SearchCharacterProximity;
use App\Models\Part;
use App\Models\Part\Scopes\FunctionalFirst;
use App\Models\Scopes\OldestKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Str;
use Tests\TestCase;

class SearchCharacterProximityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_a_part_list_filtered_by_number_and_sorted_by_scopes_passed_by_parameter()
    {
        $expectedParts = Collection::make([]);

        $otherPart            = Part::factory()->number('partNumberB')->other()->create();
        $firstFunctionalPart  = Part::factory()->number('partNumberC')->create();
        $secondFunctionalPart = Part::factory()->number('partNumberA')->create();
        $newOtherPart         = Part::factory()->number('partNumberA')->other()->create();

        $expectedParts->add($firstFunctionalPart);
        $expectedParts->add($secondFunctionalPart);
        $expectedParts->add($otherPart);
        $expectedParts->add($newOtherPart);

        Part::factory()->count(2)->create();

        $search       = 'partNumber';
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        [$parts, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $search,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
            'orderScopes'     => [
                new FunctionalFirst(),
                new OldestKey(),
            ],
        ])->execute();

        $parts->each(function(Part $partResult, int $index) use ($expectedParts) {
            $part = $expectedParts->get($index);
            $this->assertEquals($part->getRouteKey(), $partResult->getRouteKey());
        });

        $this->assertSame($search, $searchTerm);
    }

    /** @test */
    public function it_applies_sort_scopes_to_the_query()
    {
        Part::factory()->number('part number')->create();

        $search       = 'part number';
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        $alphaScope = Mockery::mock(FunctionalFirst::class);
        $alphaScope->shouldReceive('apply')->withAnyArgs()->once();

        $oldestScope = Mockery::mock(OldestKey::class);
        $oldestScope->shouldReceive('apply')->withAnyArgs()->once();

        (App::make(SearchCharacterProximity::class, [
            'newSearchString' => $search,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
            'orderScopes'     => [
                $alphaScope,
                $oldestScope,
            ],
        ])->execute());
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_repeats_search_removing_the_last_character_of_the_search_string(
        string $searchString,
        array $numbers,
        int $removedCharacters,
        int $countResult
    ) {
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        foreach ($numbers as $number) {
            Part::factory()->number($number)->create();
        }

        [$parts, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $searchString,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
        ])->execute();

        $this->assertSame($parts->count(), $countResult);

        $newSearchString = Str::substr($searchString, 0, -$removedCharacters);
        $this->assertSame($searchTerm, $newSearchString);
    }

    public function dataProvider()
    {
        return [
            ['38BRG0', ['42320', '42111'], 3, 0],
            ['38BRG0', ['38B0RB', '42111'], 3, 1],
            ['38BRG0412345', ['38BRG042320', '38BRG042111'], 5, 2],
            ['38BRG0412345678901', ['38BRG042320', '38BRG042111'], 10, 0],
            ['38BRG041234567890', ['38BRG042320', '38BRG042111'], 10, 2],
        ];
    }
}
