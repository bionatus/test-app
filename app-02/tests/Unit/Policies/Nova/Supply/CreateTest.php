<?php

namespace Tests\Unit\Policies\Nova\Supply;

use App\Policies\Nova\SupplyPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_supply()
    {
        $policy = new SupplyPolicy();
        $user   = Mockery::mock(User::class);

        $this->assertTrue($policy->create($user));
    }
}
