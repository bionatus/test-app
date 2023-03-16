<?php

namespace Tests\Unit\Policies\Nova\SupplyCategory;

use App\Models\SupplyCategory;
use App\Policies\Nova\SupplyCategoryPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_allows_to_view_a_supply_category()
    {
        $policy         = new SupplyCategoryPolicy();
        $user           = Mockery::mock(User::class);
        $supplyCategory = Mockery::mock(SupplyCategory::class);

        $this->assertTrue($policy->view($user, $supplyCategory));
    }
}
