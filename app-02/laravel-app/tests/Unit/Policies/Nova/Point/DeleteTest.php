<?php

namespace Tests\Unit\Policies\Nova\Point;

use App\Models\Point;
use App\Policies\Nova\PointPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_to_delete_a_point()
    {
        $policy = new PointPolicy();
        $user   = Mockery::mock(User::class);
        $point  = Mockery::mock(Point::class);

        $this->assertFalse($policy->delete($user, $point));
    }
}
