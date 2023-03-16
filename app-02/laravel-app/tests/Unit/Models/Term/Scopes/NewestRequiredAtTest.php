<?php

namespace Tests\Unit\Models\Term\Scopes;

use App\Models\Term;
use App\Models\Term\Scopes\NewestRequiredAt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NewestRequiredAtTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_newest_required_at()
    {
        $firstTerm  = Term::factory()->create(['required_at' => Carbon::now()]);
        $secondTerm = Term::factory()->create(['required_at' => Carbon::now()->subDay()]);
        $thirdTerm  = Term::factory()->create(['required_at' => Carbon::now()->addDay()]);

        $expectedTermsOrder = collection::make([
                $thirdTerm,
                $firstTerm,
                $secondTerm,
            ]);

        $orderedTerms = Term::scoped(new NewestRequiredAt())->get();

        $orderedTerms->each(function(Term $term, int $index) use ($expectedTermsOrder) {
            $expectedTerm = $expectedTermsOrder->get($index);
            $this->assertSame($term->getKey(), $expectedTerm->getKey());
        });
    }
}
