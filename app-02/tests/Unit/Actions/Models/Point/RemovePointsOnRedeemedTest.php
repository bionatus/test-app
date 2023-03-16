<?php

namespace Tests\Unit\Actions\Models\Point;

use App\Actions\Models\Point\RemovePointsOnRedeemed;
use App\Models\AppSetting;
use App\Models\Level;
use App\Models\User;
use App\Models\XoxoRedemption;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RemovePointsOnRedeemedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_removes_points_on_redeemed_setting_correct_level()
    {
        AppSetting::factory()->create(['slug' => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER, 'value' => $multiplier = 1]);

        $level = Mockery::mock(Level::class);
        $level->shouldReceive('getAttribute')->once()->with('coefficient')->andReturn($coefficient = 1);

        $pointsRelationship = Mockery::mock(HasMany::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('processLevel')->once()->withNoArgs();
        $user->shouldReceive('currentLevel')->once()->withNoArgs()->andReturn($level);
        $user->shouldReceive('points')->once()->withNoArgs()->andReturn($pointsRelationship);

        $xoxoRedemption = Mockery::mock(XoxoRedemption::class);
        $xoxoRedemption->shouldReceive('getAttribute')->once()->with('value_denomination')->andReturn(10);
        $xoxoRedemption->shouldReceive('getKey')->once()->andReturn($objectId = 1);

        $attributes = [
            'object_type'     => 'xoxo_redemption',
            'object_id'       => $objectId,
            'action'          => 'redeemed',
            'coefficient'     => $coefficient,
            'multiplier'      => $multiplier,
            'points_redeemed' => 1000,
        ];
        $pointsRelationship->shouldReceive('create')->once()->with($attributes);

        (new RemovePointsOnRedeemed($user, $xoxoRedemption))->execute();
    }
}
