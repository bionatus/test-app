<?php

namespace Tests\Unit\Rules\XoxoRedemption;

use App\Models\Point;
use App\Models\User;
use App\Rules\XoxoRedemption\AvailablePoints;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailablePointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_true_if_there_are_enough_funds()
    {
        $user = User::factory()->create();
        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 10000]);
        $this->login($user);

        $rule = new AvailablePoints();

        $this->assertTrue($rule->passes('denomination', 10));
    }

    /** @test */
    public function it_returns_false_if_there_are_not_enough_funds()
    {
        $user = User::factory()->create();
        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 100]);
        $this->login($user);

        $rule = new AvailablePoints();

        $this->assertFalse($rule->passes('denomination', 10));
    }
}
