<?php

namespace Tests\Unit\Models\SupplyCategoryView;

use App\Models\SupplyCategory;
use App\Models\SupplyCategoryView;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplyCategoryView $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplyCategoryView::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supply_category()
    {
        $related = $this->instance->supplyCategory()->first();

        $this->assertInstanceOf(SupplyCategory::class, $related);
    }
}
