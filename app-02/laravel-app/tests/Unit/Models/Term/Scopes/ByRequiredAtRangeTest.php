<?php

namespace Tests\Unit\Models\Term\Scopes;

use App\Models\Term;
use App\Models\Term\Scopes\ByRequiredAtRange;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByRequiredAtRangeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_terms_by_required_at_date_range()
    {
        $today = Carbon::now();
        Term::factory()->create(['required_at' => Carbon::now()->addDay()]);
        Term::factory()->create(['required_at' => $today]);
        Term::factory()->create(['required_at' => Carbon::now()->subDay()]);

        $filtered = Term::scoped(new ByRequiredAtRange($today))->get();

        $this->assertCount(2, $filtered);
    }
}
