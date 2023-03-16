<?php

namespace Tests\Unit\Policies\Nova\User;

use App\Policies\Nova\UserPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_user()
    {
        $policy = new UserPolicy();
        $user   = Mockery::mock(User::class);

        $this->assertTrue($policy->create($user));
    }
}
