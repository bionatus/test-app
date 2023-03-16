<?php

namespace Tests\Unit\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\Oem\Scopes\ByModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_oem_by_model()
    {
        Oem::factory()->create(['model' => 'Test Name ObjectiveOne Model']);
        Oem::factory()->create(['model' => 'Another Model']);
        Oem::factory()->create(['model' => 'Lorem Ipsum objective']);

        $filtered = Oem::scoped(new ByModel("objective"))->get();

        $this->assertCount(2, $filtered);
    }
}
