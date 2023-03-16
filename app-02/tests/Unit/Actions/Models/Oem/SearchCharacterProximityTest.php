<?php

namespace Tests\Unit\Actions\Models\Oem;

use App;
use App\Actions\Models\Oem\SearchCharacterProximity;
use App\Models\Oem;
use App\Models\Oem\Scopes\Alphabetically;
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
    public function it_gets_a_list_of_published_oems_filtered_by_model_and_sorted_by_model_and_model_notes_alphabetically_and_id_with_a_search_term(
    )
    {
        $expectedOems = Collection::make([]);
        $expectedOems->add(Oem::factory()->create(['model' => 'oem model b', 'model_notes' => 'oem model b']));
        $expectedOems->add(Oem::factory()->create([
            'id'          => 10,
            'model'       => 'oem model c',
            'model_notes' => 'oem model c1',
        ]));
        $expectedOems->add(Oem::factory()->create([
            'id'          => 20,
            'model'       => 'oem model c',
            'model_notes' => 'oem model c2',
        ]));
        $expectedOems->add(Oem::factory()->create(['model' => 'oem model a']));

        Oem::factory()->count(2)->create([
            'status'      => null,
            'model'       => 'oem model d',
            'model_notes' => 'oem model d',
        ]);

        Oem::factory()->count(2)->create();

        $search       = 'oem model';
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        [$oems, $searchTerm] = (App::make(SearchCharacterProximity::class, [
            'newSearchString' => $search,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
        ])->execute());

        $oems->each(function (Oem $oemResult, int $index) use ($expectedOems) {
            $oem = $expectedOems->get($index);
            $this->assertEquals($oem->getRouteKey(), $oemResult->getRouteKey());
        });

        $this->assertSame($search, $searchTerm);
    }

    /** @test */
    public function it_applies_sort_scopes_to_the_query()
    {
        Oem::factory()->create([
            'id'          => 10,
            'model'       => 'oem model c',
            'model_notes' => 'oem model c1',
        ]);

        $search       = 'oem model';
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        $alphaScope = Mockery::mock(Alphabetically::class);
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
        array $models,
        int $removedCharacters,
        int $countResult
    ) {
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        foreach ($models as $model) {
            Oem::factory()->live()->create(['model' => $model]);
        }

        [$oems, $searchTerm] = (App::make(SearchCharacterProximity::class, [
            'newSearchString' => $searchString,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
            'orderScopes'     => [
                new Alphabetically('model'),
                new Alphabetically('model_notes'),
                new OldestKey(),
            ],
        ])->execute());

        $this->assertSame($oems->count(), $countResult);

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
