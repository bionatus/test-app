<?php

namespace Tests\Unit\Types;

use App\Types\Point;
use Tests\TestCase;

class PointTest extends TestCase
{
    /** @test */
    public function it_returns_points()
    {
        $points = 10;

        $pointData = new Point($points, 0.3, 1);

        $this->assertEquals($points, $pointData->points());
    }

    /** @test */
    public function it_returns_coefficient()
    {
        $coefficient = 0.3;

        $pointData = new Point(10, $coefficient, 1);

        $this->assertEquals($coefficient, $pointData->coefficient());
    }

    /** @test */
    public function it_returns_multiplier()
    {
        $multiplier = 1;

        $pointData = new Point(10, 0.1, $multiplier);

        $this->assertEquals($multiplier, $pointData->multiplier());
    }
}
