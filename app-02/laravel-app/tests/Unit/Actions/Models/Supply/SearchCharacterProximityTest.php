<?php

namespace Tests\Unit\Actions\Models\Supply;

use App;
use App\Actions\Models\Supply\SearchCharacterProximity;
use App\Models\CartSupplyCounter;
use App\Models\Scopes\Alphabetically;
use App\Models\Supply;
use App\Models\Supply\Scopes\MostAddedToCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Str;
use Tests\TestCase;

class SearchCharacterProximityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_a_supplies_list_filtered_by_name_and_visibility()
    {
        $supply1                = Supply::factory()->name('Fake cable C')->visible()->create();
        $secondFunctionalSupply = Supply::factory()->name('Fake cable A')->visible()->create();
        $otherSupply            = Supply::factory()->name('Fake cable B')/*->visible()*/ ->create();
        $otherSupply1           = Supply::factory()->name('Fake_cable_1')->visible()->create();
        $otherSupply2           = Supply::factory()->name('Fake_cable_2')->visible()->create();

        CartSupplyCounter::factory()->usingSupply($supply1)->count(3)->create();
        CartSupplyCounter::factory()->usingSupply($secondFunctionalSupply)->count(2)->create();
        CartSupplyCounter::factory()->usingSupply($otherSupply)->count(1)->create();

        $expectedSupplies = Collection::make([
            $supply1,
            $secondFunctionalSupply,
            $otherSupply1,
            $otherSupply2,
        ]);

        Supply::factory()->count(2)->create();

        $search       = 'Cable';
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        [$supplies, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $search,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
        ])->execute();

        $supplies->each(function(Supply $supplyResult, int $index) use ($expectedSupplies) {
            $supply = $expectedSupplies->get($index);
            $this->assertEquals($supply->getRouteKey(), $supplyResult->getRouteKey());
        });
        $this->assertSame($search, $searchTerm);
    }

    /** @test */
    public function it_applies_sort_scopes_to_the_query()
    {
        Supply::factory()->name('supply name')->visible()->create();

        $search       = 'supply name';
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        $mostAdded = Mockery::mock(MostAddedToCart::class);
        $mostAdded->shouldReceive('apply')->withAnyArgs()->once();

        $alphabetically = Mockery::mock(Alphabetically::class);
        $alphabetically->shouldReceive('apply')->withAnyArgs()->once();

        (App::make(SearchCharacterProximity::class, [
            'newSearchString' => $search,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
            'orderScopes'     => [
                $mostAdded,
                $alphabetically,
            ],
        ])->execute());
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_repeats_search_removing_the_last_character_of_the_search_string(
        string $searchString,
        array $names,
        int $removedCharacters,
        int $countResult
    ) {
        $repeat       = 0;
        $maxSearch    = 10;
        $minCharacter = 3;

        foreach ($names as $name) {
            $supply = Supply::factory()->name($name)->visible()->create();
            CartSupplyCounter::factory()->usingSupply($supply)->create();
        }

        [$supplies, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $searchString,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
        ])->execute();

        $this->assertSame($supplies->count(), $countResult);
        $newSearchString = Str::substr($searchString, 0, -$removedCharacters);
        $this->assertSame($searchTerm, $newSearchString);
    }

    public function dataProvider()
    {
        return [
            ['High Voltage Cable', ['Elect', 'Conne'], 10, 0],
            ['Cable', ['Cabxyz', 'Connec'], 2, 1],
            ['Cable412345', ['Cable42320', 'Cable42111'], 5, 2],
            ['Cableabcdefghijkl', ['Cablevwxyz', 'Cablemnopq'], 10, 0],
            ['Cableabcdefghij', ['Cablevwxyz', 'Cablemnopq'], 10, 2],
        ];
    }
}
