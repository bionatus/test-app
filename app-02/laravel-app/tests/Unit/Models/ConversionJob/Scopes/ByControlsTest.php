<?php

namespace Tests\Unit\Models\ConversionJob\Scopes;

use App\Models\ConversionJob;
use App\Models\ConversionJob\Scopes\ByControls;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByControlsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_controls()
    {
        ConversionJob::factory()->count(10)->create();
        ConversionJob::factory()->create(['control'=>'standard']);
        ConversionJob::factory()->create(['control'=>'optional']);

        $this->assertCount(2, ConversionJob::scoped(new ByControls(['standard', 'optional']))->get());
    }
}
