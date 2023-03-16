<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\ByNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;
use Tests\TestCase;

class ByNumberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_complete_number()
    {
        $part = Part::factory()->create();
        Part::factory()->create();

        $filtered = Part::scoped(new ByNumber($part->number))->get();

        $this->assertCount(1, $filtered);
        $this->assertInstanceOf(Part::class, $filtered->first());
        $this->assertSame($part->getKey(), $filtered->first()->getKey());
    }

    /** @test */
    public function it_filters_by_partial_number()
    {
        $part = Part::factory()->create();
        Part::factory()->create();

        $number   = Str::substr($part->number, 3, 6);
        $filtered = Part::scoped(new ByNumber($number))->get();

        $this->assertCount(1, $filtered);
        $this->assertInstanceOf(Part::class, $filtered->first());
        $this->assertSame($part->getKey(), $filtered->first()->getKey());
    }
}
