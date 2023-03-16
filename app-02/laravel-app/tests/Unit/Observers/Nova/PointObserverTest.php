<?php

namespace Tests\Unit\Observers\Nova;

use App\Models\AppSetting;
use App\Models\Level;
use App\Models\Point;
use App\Models\User;
use App\Nova\Observers\PointObserver;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PointObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sets_point_fields_when_saving()
    {
        Level::factory()->create([
            'slug'        => 'level-0',
            'from'        => 0,
            'to'          => 999,
            'coefficient' => 0.5,
        ]);

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        $user                 = User::factory()->create();
        $point                = new Point();
        $point->points_earned = 7;
        $point->user_id       = $user->getKey();

        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        Auth::shouldReceive('user')->withNoArgs()->once()->andReturns($user);

        $observer = new PointObserver();
        $observer->saving($point);

        $this->assertEquals('adjustment', $point->action);
        $this->assertEquals(1, $point->coefficient);
        $this->assertEquals(1, $point->multiplier);
    }

    /** @test */
    public function it_updates_user_current_level_when_saved()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('processLevel')->withNoArgs()->once()->andReturnNull();

        $point = Mockery::mock(Point::class);
        $point->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);

        $observer = new PointObserver();
        $observer->saved($point);
    }
}
