<?php

namespace Tests\Unit\Policies\Nova\User;

use App\Policies\Nova\UserPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_allows_to_view()
    {
        $policy = new UserPolicy();
        $user   = Mockery::mock(User::class);

        $this->assertTrue($policy->viewAny($user));
    }
}
