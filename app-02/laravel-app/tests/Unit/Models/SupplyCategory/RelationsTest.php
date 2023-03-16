<?php

namespace Tests\Unit\Models\SupplyCategory;

use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\SupplyCategoryView;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplyCategory $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplyCategory::factory()->create();
    }

    /** @test */
    public function it_has_supplies()
    {
        Supply::factory()->usingSupplyCategory($this->instance)->count(10)->create();

        $related = $this->instance->supplies()->get();

        $this->assertCorrectRelation($related, Supply::class);
    }

    /** @test */
    public function it_has_children()
    {
        SupplyCategory::factory()->usingParent($this->instance)->count(10)->create();

        $related = $this->instance->children()->get();

        $this->assertCorrectRelation($related, SupplyCategory::class);
    }

    /** @test */
    public function it_belongs_to_a_parent()
    {
        $subcategory = SupplyCategory::factory()->usingParent($this->instance)->create();

        $related = $subcategory->parent()->first();

        $this->assertInstanceOf(SupplyCategory::class, $related);
    }

    /** @test */
    public function it_has_supply_category_views()
    {
        SupplyCategoryView::factory()->usingSupplyCategory($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supplyCategoryViews()->get();

        $this->assertCorrectRelation($related, SupplyCategoryView::class);
    }
}
