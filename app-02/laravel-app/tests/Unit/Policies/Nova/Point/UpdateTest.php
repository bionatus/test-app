<?php

namespace Tests\Unit\Policies\Nova\Point;

use App\Models\Point;
use App\Policies\Nova\PointPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_does_not_allows_to_update_a_point()
    {
        $policy = new PointPolicy();
        $user   = Mockery::mock(User::class);
        $point  = Mockery::mock(Point::class);

        $this->assertFalse($policy->update($user, $point));
    }
}
