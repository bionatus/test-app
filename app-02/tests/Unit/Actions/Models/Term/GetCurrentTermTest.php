<?php

namespace Tests\Unit\Actions\Models\Term;

use App\Actions\Models\Term\GetCurrentTerm;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class GetCurrentTermTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_knows_the_current_term()
    {
        $this->assertSame(App::make(GetCurrentTerm::class)->execute(), null);

        $today = Carbon::now();
        Term::factory()->create(['required_at' => Carbon::now()->addDay()]);
        Term::factory()->create(['required_at' => Carbon::now()->subDay()]);
        Term::factory()->create(['title' => 'Title Term', 'required_at' => $today]);
        $expectedTerm = Term::factory()->create(['title' => 'Title Term Second', 'required_at' => $today]);

        $this->assertSame(App::make(GetCurrentTerm::class)->execute()->getKey(), $expectedTerm->getKey());
    }
}
