<?php

namespace Tests\Unit\Models\InstrumentSupportCallCategory;

use App\Models\Instrument;
use App\Models\InstrumentSupportCallCategory;
use App\Models\SupportCallCategory;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property InstrumentSupportCallCategory $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = InstrumentSupportCallCategory::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_instrument()
    {
        $related = $this->instance->instrument()->first();

        $this->assertInstanceOf(Instrument::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_support_call_category()
    {
        $related = $this->instance->supportCallCategory()->first();

        $this->assertInstanceOf(SupportCallCategory::class, $related);
    }
}
