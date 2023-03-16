<?php

namespace Tests\Unit\Policies\Nova\SupplyCategory;

use App\Policies\Nova\SupplyCategoryPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_supply_category()
    {
        $policy = new SupplyCategoryPolicy();
        $user   = Mockery::mock(User::class);

        $this->assertTrue($policy->create($user));
    }
}
