<?php

namespace Tests\Unit\Models\SupportCallCategory;

use App\Models\Instrument;
use App\Models\InstrumentSupportCallCategory;
use App\Models\SupportCallCategory;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupportCallCategory $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupportCallCategory::factory()->create();
    }

    /** @test */
    public function it_has_children()
    {
        SupportCallCategory::factory()->usingParent($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->children()->get();

        $this->assertCorrectRelation($related, SupportCallCategory::class);
    }

    /** @test */
    public function it_belongs_to_a_parent()
    {
        $subcategory = SupportCallCategory::factory()->usingParent($this->instance)->create();

        $related = $subcategory->parent()->first();

        $this->assertInstanceOf(SupportCallCategory::class, $related);
    }

    /** @test */
    public function it_has_instruments()
    {
        InstrumentSupportCallCategory::factory()->usingSupportCallCategory($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->instruments()->get();

        $this->assertCorrectRelation($related, Instrument::class);
    }

    /** @test */
    public function it_has_instrument_support_call_categories()
    {
        InstrumentSupportCallCategory::factory()->usingSupportCallCategory($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->instrumentSupportCallCategories()->get();

        $this->assertCorrectRelation($related, InstrumentSupportCallCategory::class);
    }
}
