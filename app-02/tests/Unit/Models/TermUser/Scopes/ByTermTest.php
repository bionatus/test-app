<?php

namespace Tests\Unit\Models\TermUser\Scopes;

use App\Models\Term;
use App\Models\TermUser;
use App\Models\TermUser\Scopes\ByTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTermTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_registers_that_has_related_term()
    {
        $objectiveTerm = Term::factory()->create();
        TermUser::factory()->count(7)->create();
        TermUser::factory()->usingTerm($objectiveTerm)->create();

        $this->assertSame($objectiveTerm->getKey(),
            TermUser::scoped(new ByTerm($objectiveTerm))->first()->term_id);
    }
}
