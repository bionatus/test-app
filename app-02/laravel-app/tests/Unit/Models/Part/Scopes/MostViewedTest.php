<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\MostViewed;
use App\Models\PartDetailCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MostViewedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sorts_a_list_of_parts_by_most_viewed()
    {
        $firstPart = Part::factory()->number('partNumberC')->create();
        PartDetailCounter::factory()->usingPart($firstPart)->count(4)->create();

        $secondPart = Part::factory()->number('partNumberA')->create();
        PartDetailCounter::factory()->usingPart($secondPart)->count(5)->create();

        $thirdPart = Part::factory()->number('partNumberB')->other()->create();
        PartDetailCounter::factory()->usingPart($thirdPart)->count(3)->create();

        $fourthPart = Part::factory()->number('partNumberD')->create();

        $fifthPart  = Part::factory()->number('partNumber')->create();
        PartDetailCounter::factory()->usingPart($fifthPart)->count(2)->create();

        $expectedParts = Collection::make([
            $secondPart,
            $firstPart,
            $thirdPart,
            $fifthPart,
            $fourthPart,
        ]);

        $parts = Part::scoped(new MostViewed())->get();
        $parts->each(function(Part $partResult, int $index) use ($expectedParts) {
            $part = $expectedParts->get($index);
            $this->assertEquals($part->getRouteKey(), $partResult->getRouteKey());
        });
    }
}
