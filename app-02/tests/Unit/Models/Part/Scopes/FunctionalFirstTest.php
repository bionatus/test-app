<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\FunctionalFirst;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunctionalFirstTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_functional_first()
    {
        $otherParts      = Part::factory()->other()->count(2)->create()->fresh();
        $functionalParts = Part::factory()->functional()->count(3)->create()->fresh();

        $parts = Part::scoped(new FunctionalFirst())->get();

        $this->assertEquals($functionalParts->push(...$otherParts)->toArray(), $parts->toArray());
    }
}
