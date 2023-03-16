<?php

namespace Tests\Unit\Rules\SupportCallCategory;

use App\Models\SupportCallCategory;
use App\Rules\SupportCallCategory\ValidCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_pass_if_category_does_not_exist()
    {
        $rule = new ValidCategory();

        $this->assertFalse($rule->passes('attribute', 'category'));
        $this->assertSame('Invalid support call category.', $rule->message());
    }

    /** @test */
    public function it_does_not_pass_if_category_has_children()
    {
        $category = SupportCallCategory::factory()->create();
        SupportCallCategory::factory()->usingParent($category)->create();

        $rule = new ValidCategory();

        $this->assertFalse($rule->passes('attribute', $category->getRouteKey()));
        $this->assertSame('Invalid support call category.', $rule->message());
    }

    /** @test */
    public function it_passes_for_a_category_without_children()
    {
        $category = SupportCallCategory::factory()->create();

        $rule = new ValidCategory();

        $this->assertTrue($rule->passes('attribute', $category->getRouteKey()));
    }

    /** @test */
    public function it_passes_for_an_oem_category()
    {
        $rule = new ValidCategory();

        $this->assertTrue($rule->passes('attribute', 'oem'));
    }

    /** @test */
    public function it_passes_for_a_missing_oem_category()
    {
        $rule = new ValidCategory();

        $this->assertTrue($rule->passes('attribute', 'missing-oem'));
    }
}
