<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\LastViewed;
use App\Models\PartDetailCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LastViewedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_groups_and_sorts_by_last_part_viewed()
    {
        $part      = Part::factory()->create();
        $otherPart = Part::factory()->create();

        $lastPartDetailCounter = PartDetailCounter::factory()
            ->usingPart($otherPart)
            ->create(['created_at' => Carbon::now()->subDay()]);

        $firstPartDetailCounter = PartDetailCounter::factory()
            ->usingPart($part)
            ->create(['created_at' => Carbon::now()]);
        PartDetailCounter::factory()->usingPart($part)->create(['created_at' => Carbon::now()->subDays(2)]);
        PartDetailCounter::factory()->usingPart($part)->create(['created_at' => Carbon::now()->subDay()]);

        $expected = Collection::make([$firstPartDetailCounter->part, $lastPartDetailCounter->part]);

        $result = Part::scoped(new LastViewed())->get();

        $this->assertCount(2, $result);
        $result->each(function(Part $part) use ($expected) {
            /** @var Part $partExpected */
            $partExpected = $expected->shift();
            $this->assertSame($partExpected->getRouteKey(), $part->getRouteKey());
        });
    }
}
