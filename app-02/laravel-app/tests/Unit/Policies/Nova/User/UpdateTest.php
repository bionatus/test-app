<?php

namespace Tests\Unit\Policies\Nova\User;

use App\Models\User as UserModel;
use App\Policies\Nova\UserPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_allows_to_update_a_user()
    {
        $policy    = new UserPolicy();
        $user      = Mockery::mock(User::class);
        $userModel = Mockery::mock(UserModel::class);

        $this->assertTrue($policy->update($user, $userModel));
    }
}
