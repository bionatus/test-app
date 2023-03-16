<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\Number;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_number_alphabetically()
    {
        Part::factory()->functional()->count(10)->create();
        $parts   = Part::orderBy('number')->get();
        $ordered = Part::scoped(new Number())->get();

        $ordered->each(function(Part $part) use ($parts) {
            $this->assertSame($parts->shift()->getKey(), $part->getKey());
        });
    }
}
