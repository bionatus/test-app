<?php

namespace Tests\Unit\Observers;

use App\Models\CompanyUser;
use App\Models\User;
use App\Observers\CompanyUserObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CompanyUserObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_verifies_user_when_saved()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('verify')->withNoArgs()->once()->andReturnSelf();
        $user->shouldReceive('save')->withNoArgs()->once();

        $model = Mockery::mock(CompanyUser::class);
        $model->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);

        $observer = new CompanyUserObserver();

        $observer->saved($model);
    }

    /** @test */
    public function it_verifies_user_when_deleted()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('verify')->withNoArgs()->once()->andReturnSelf();
        $user->shouldReceive('save')->withNoArgs()->once();

        $model = Mockery::mock(CompanyUser::class);
        $model->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);

        $observer = new CompanyUserObserver();

        $observer->deleted($model);
    }
}
