<?php

namespace Tests\Unit\Policies\AppVersion;

use App\Models\AppVersion;
use App\Policies\Nova\AppVersionPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_to_delete_an_app_version()
    {
        $policy     = new AppVersionPolicy();
        $user       = Mockery::mock(User::class);
        $appVersion = Mockery::mock(AppVersion::class);

        $this->assertFalse($policy->delete($user, $appVersion));
    }
}
