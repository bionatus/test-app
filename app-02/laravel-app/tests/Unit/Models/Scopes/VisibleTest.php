<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\Visible;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_only_visible_on_supplies()
    {
        Supply::factory()->count(10)->create();
        Supply::factory()->count(3)->visible()->create();

        $supplies = Supply::scoped(new Visible())->get();

        $this->assertCount(3, $supplies);
    }

    /** @test */
    public function it_filters_only_visible_on_supply_categories()
    {
        SupplyCategory::factory()->count(10)->create();
        SupplyCategory::factory()->count(3)->visible()->create();

        $supplyCategories = SupplyCategory::scoped(new Visible())->get();

        $this->assertCount(3, $supplyCategories);
    }
}
