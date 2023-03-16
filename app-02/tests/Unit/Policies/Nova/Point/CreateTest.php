<?php

namespace Tests\Unit\Policies\Nova\Point;

use App\Policies\Nova\PointPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_point()
    {
        $policy = new PointPolicy();
        $point  = Mockery::mock(User::class);

        $this->assertTrue($policy->create($point));
    }
}
