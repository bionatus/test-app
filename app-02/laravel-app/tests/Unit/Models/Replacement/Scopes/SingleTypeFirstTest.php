<?php

namespace Tests\Unit\Models\Replacement\Scopes;

use App\Models\Part;
use App\Models\Replacement;
use App\Models\Replacement\Scopes\SingleTypeFirst;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SingleTypeFirstTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_single_type()
    {
        $part                     = Part::factory()->create();
        $singleReplacement        = Replacement::factory()->single()->usingPart($part)->create();
        $groupedReplacement       = Replacement::factory()->grouped()->usingPart($part)->create();
        $anotherSingleReplacement = Replacement::factory()->single()->usingPart($part)->create();

        $expectedReplacements = Collection::make([$singleReplacement, $anotherSingleReplacement, $groupedReplacement]);

        $replacements = Replacement::scoped(new SingleTypeFirst())->get();

        $replacements->each(function(Replacement $replacement, int $index) use ($expectedReplacements) {
            $this->assertSame($expectedReplacements->get($index)->getKey(), $replacement->getKey());
        });
    }
}
