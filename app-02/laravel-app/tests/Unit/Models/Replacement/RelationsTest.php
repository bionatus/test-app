<?php

namespace Tests\Unit\Models\Replacement;

use App\Models\GroupedReplacement;
use App\Models\ItemOrder;
use App\Models\ItemOrderSnap;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\ReplacementNote;
use App\Models\SingleReplacement;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Replacement $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Replacement::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }

    /** @test */
    public function it_is_a_single_replacement()
    {
        SingleReplacement::factory()->usingReplacement($this->instance)->create();

        $related = $this->instance->singleReplacement()->first();

        $this->assertInstanceOf(SingleReplacement::class, $related);
    }

    /** @test */
    public function it_is_a_grouped_replacement()
    {
        GroupedReplacement::factory()->usingReplacement($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->groupedReplacements()->get();

        $this->assertCorrectRelation($related, GroupedReplacement::class);
    }

    /** @test */
    public function it_has_a_note()
    {
        ReplacementNote::factory()->usingReplacement($this->instance)->create();

        $related = $this->instance->note()->first();

        $this->assertInstanceOf(ReplacementNote::class, $related);
    }

    /** @test */
    public function it_has_item_orders()
    {
        ItemOrder::factory()->usingReplacement($this->instance)->count(10)->createQuietly();

        $related = $this->instance->itemOrders()->get();

        $this->assertCorrectRelation($related, ItemOrder::class);
    }

    /** @test */
    public function it_has_item_order_snaps()
    {
        ItemOrderSnap::factory()->usingReplacement($this->instance)->count(10)->createQuietly();

        $related = $this->instance->itemOrderSnaps()->get();

        $this->assertCorrectRelation($related, ItemOrderSnap::class);
    }
}
