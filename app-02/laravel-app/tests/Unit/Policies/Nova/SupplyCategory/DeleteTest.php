<?php

namespace Tests\Unit\Policies\Nova\SupplyCategory;

use App\Models\SupplyCategory;
use App\Policies\Nova\SupplyCategoryPolicy;
use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allows_to_delete_a_supply_category_that_has_children()
    {
        $children = Mockery::mock(HasMany::class);
        $children->shouldReceive('count')->withNoArgs()->once()->andReturn(1);
        $supplyCategory = Mockery::mock(SupplyCategory::class);
        $supplyCategory->shouldReceive('children')->withNoArgs()->once()->andReturn($children);
        $user = Mockery::mock(User::class);

        $policy = new SupplyCategoryPolicy();

        $this->assertFalse($policy->delete($user, $supplyCategory));
    }

    /** @test */
    public function it_does_not_allows_to_delete_a_supply_category_that_has_supplies()
    {
        $children = Mockery::mock(HasMany::class);
        $children->shouldReceive('count')->withNoArgs()->once()->andReturn(0);
        $supplies = Mockery::mock(HasMany::class);
        $supplies->shouldReceive('count')->withNoArgs()->once()->andReturn(1);
        $supplyCategory = Mockery::mock(SupplyCategory::class);
        $supplyCategory->shouldReceive('children')->withNoArgs()->once()->andReturn($children);
        $supplyCategory->shouldReceive('supplies')->withNoArgs()->once()->andReturn($supplies);
        $user = Mockery::mock(User::class);

        $policy = new SupplyCategoryPolicy();

        $this->assertFalse($policy->delete($user, $supplyCategory));
    }

    /** @test */
    public function it_allows_to_delete_a_supply_category_that_does_not_have_children_nor_supplies()
    {
        $children = Mockery::mock(HasMany::class);
        $children->shouldReceive('count')->withNoArgs()->once()->andReturn(0);
        $supplies = Mockery::mock(HasMany::class);
        $supplies->shouldReceive('count')->withNoArgs()->once()->andReturn(0);
        $supplyCategory = Mockery::mock(SupplyCategory::class);
        $supplyCategory->shouldReceive('children')->withNoArgs()->once()->andReturn($children);
        $supplyCategory->shouldReceive('supplies')->withNoArgs()->once()->andReturn($supplies);
        $user = Mockery::mock(User::class);

        $policy = new SupplyCategoryPolicy();

        $this->assertTrue($policy->delete($user, $supplyCategory));
    }
}
