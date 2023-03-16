<?php

namespace Tests\Unit\Rules\Supply;

use App\Models\SupplyCategory;
use App\Rules\Supply\ValidSupplyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidSupplyCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_pass_if_the_supply_category_has_children()
    {
        $category = SupplyCategory::factory()->create();
        SupplyCategory::factory()->usingParent($category)->create();

        $rule = new ValidSupplyCategory();

        $this->assertFalse($rule->passes('attribute', $category->getRouteKey()));
        $this->assertSame('Invalid supply category.', $rule->message());
    }

    /** @test */
    public function it_passes_for_a_category_without_children()
    {
        $category = SupplyCategory::factory()->create();

        $rule = new ValidSupplyCategory();

        $this->assertTrue($rule->passes('attribute', $category->getRouteKey()));
    }
}
