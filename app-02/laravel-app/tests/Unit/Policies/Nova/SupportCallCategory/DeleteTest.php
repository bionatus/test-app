<?php

namespace Tests\Unit\Policies\Nova\SupportCallCategory;

use App\Models\SupportCallCategory;
use App\Policies\Nova\SupportCallCategoryPolicy;
use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_to_delete_a_support_call_category_that_has_children()
    {
        $children = Mockery::mock(HasMany::class);
        $children->shouldReceive('doesntExist')->withNoArgs()->once()->andReturnFalse();

        $supportCallCategory = Mockery::mock(SupportCallCategory::class);
        $supportCallCategory->shouldReceive('children')->withNoArgs()->once()->andReturn($children);

        $policy = new SupportCallCategoryPolicy();

        $this->assertFalse($policy->delete(new User(), $supportCallCategory));
    }

    /** @test */
    public function it_allows_to_delete_a_support_call_category_that_does_not_have_children()
    {
        $children = Mockery::mock(HasMany::class);
        $children->shouldReceive('doesntExist')->withNoArgs()->once()->andReturnTrue();

        $supportCallCategory = Mockery::mock(SupportCallCategory::class);
        $supportCallCategory->shouldReceive('children')->withNoArgs()->once()->andReturn($children);

        $policy = new SupportCallCategoryPolicy();

        $this->assertTrue($policy->delete(new User(), $supportCallCategory));
    }
}
