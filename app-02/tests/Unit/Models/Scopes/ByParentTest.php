<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\ByParent;
use App\Models\SupplyCategory;
use App\Models\SupportCallCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByParentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_supply_categories_by_parent()
    {
        $expectedCategories = SupplyCategory::factory()->count(3)->create();

        $category = $expectedCategories->first();

        SupplyCategory::factory()->usingParent($category)->count(5)->create();

        $filtered = SupplyCategory::scoped(new ByParent())->get();

        $this->assertCount(3, $filtered);
    }

    /** @test */
    public function it_filters_support_call_categories_by_parent()
    {
        $expectedCategories = SupportCallCategory::factory()->count(3)->create();

        $category = $expectedCategories->first();

        SupportCallCategory::factory()->usingParent($category)->count(5)->create();

        $filtered = SupportCallCategory::scoped(new ByParent())->get();

        $this->assertCount(3, $filtered);
    }
}
