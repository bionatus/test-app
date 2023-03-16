<?php

namespace Tests\Unit\Models\Warning\Scopes;

use App\Models\Warning;
use App\Models\Warning\Scopes\ByTitles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTitlesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_titles()
    {
        Warning::factory()->count(10)->create();
        Warning::factory()->create(['title' => 'hard']);
        Warning::factory()->create(['title' => 'soft']);

        $this->assertCount(2, Warning::scoped(new ByTitles(['hard', 'soft']))->get());
    }
}
