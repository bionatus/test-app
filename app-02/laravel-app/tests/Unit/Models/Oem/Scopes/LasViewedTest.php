<?php

namespace Tests\Unit\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\Oem\Scopes\LastViewed;
use App\Models\OemDetailCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LasViewedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function its_oems_most_viewed_sorted_by_visited_at_and_grouped_by_oem_id()
    {
        $oem      = Oem::factory()->create();
        $otherOem = Oem::factory()->create();

        $now = Carbon::now();

        $firstOemDetailCounter = OemDetailCounter::factory()->usingOem($oem)->create(['created_at' => $now]);
        OemDetailCounter::factory()->usingOem($oem)->create(['created_at' => $now->subDays(2)]);
        OemDetailCounter::factory()->usingOem($oem)->create(['created_at' => $now->subDay()]);
        $lastOemDetailCounter = OemDetailCounter::factory()->usingOem($otherOem)->create(['created_at' => $now]);

        $oems = Collection::make([$firstOemDetailCounter, $lastOemDetailCounter]);

        $return = Oem::scoped(new LastViewed())->get();

        $this->assertCount(2, $return);

        $return->each(function(Oem $oem) use ($oems) {
            $oemDetailCounter = $oems->shift();
            $this->assertSame($oemDetailCounter->oem_id, $oem->getKey());
            $this->assertSame($oemDetailCounter->created_at->format('Y-m-d H:i:s'), $oem->visited_at);
        });
    }
}
