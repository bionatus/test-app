<?php

namespace Tests\Unit\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\Oem\Scopes\MostViewed;
use App\Models\OemDetailCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MostViewedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sorts_a_list_of_oem_by_most_searched()
    {
        $oem = Oem::factory()->create();
        OemDetailCounter::factory()->usingOem($oem)->count(2)->create();

        $oem2 = Oem::factory()->create();
        OemDetailCounter::factory()->usingOem($oem2)->count(5)->create();

        $oem3 = Oem::factory()->create();
        OemDetailCounter::factory()->usingOem($oem3)->count(4)->create();

        $oemsExpected = Collection::make([
            $oem2,
            $oem3,
            $oem,
        ]);

        $oems = Oem::scoped(new MostViewed())->get();

        $oems->each(function(Oem $rawOemSearch) use ($oemsExpected) {
            $oem = $oemsExpected->shift();
            $this->assertSame($oem->getKey(), $rawOemSearch['id']);
        });
    }
}
