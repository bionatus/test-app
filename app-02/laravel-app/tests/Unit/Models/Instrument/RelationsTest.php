<?php

namespace Tests\Unit\Models\Instrument;

use App\Models\Instrument;
use App\Models\InstrumentSupportCallCategory;
use App\Models\SupportCallCategory;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Instrument $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Instrument::factory()->create();
    }

    /** @test */
    public function it_has_support_call_categories()
    {
        InstrumentSupportCallCategory::factory()->usingInstrument($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supportCallCategories()->get();

        $this->assertCorrectRelation($related, SupportCallCategory::class);
    }

    /** @test */
    public function it_has_instrument_support_call_categories()
    {
        InstrumentSupportCallCategory::factory()->usingInstrument($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->instrumentSupportCallCategories()->get();

        $this->assertCorrectRelation($related, InstrumentSupportCallCategory::class);
    }
}
