<?php

namespace Tests\Unit\Policies\Nova\Supply;

use App\Models\Supply;
use App\Policies\Nova\SupplyPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_allows_to_view_a_supply()
    {
        $policy = new SupplyPolicy();
        $user   = Mockery::mock(User::class);
        $supply = Mockery::mock(Supply::class);

        $this->assertTrue($policy->view($user, $supply));
    }
}
