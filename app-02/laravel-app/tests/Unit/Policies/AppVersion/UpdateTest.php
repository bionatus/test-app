<?php

namespace Tests\Unit\Policies\AppVersion;

use App\Models\AppVersion;
use App\Policies\Nova\AppVersionPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_allows_to_update_an_app_version()
    {
        $policy     = new AppVersionPolicy();
        $user       = Mockery::mock(User::class);
        $appVersion = Mockery::mock(AppVersion::class);

        $this->assertTrue($policy->update($user, $appVersion));
    }
}
