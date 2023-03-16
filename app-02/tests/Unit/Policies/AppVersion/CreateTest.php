<?php

namespace Tests\Unit\Policies\AppVersion;

use App\Policies\Nova\AppVersionPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_does_not_allows_to_create_an_app_version()
    {
        $policy = new AppVersionPolicy();
        $user   = Mockery::mock(User::class);

        $this->assertFalse($policy->create($user));
    }
}
