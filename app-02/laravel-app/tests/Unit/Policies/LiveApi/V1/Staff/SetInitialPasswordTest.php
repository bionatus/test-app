<?php

namespace Tests\Unit\Policies\LiveApi\V1\Staff;

use App\Models\Staff;
use App\Policies\LiveApi\V1\StaffPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SetInitialPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_a_never_password_set_staff_to_set_a_new_password()
    {
        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('hasSetInitialPassword')->withNoArgs()->once()->andReturnFalse();

        $policy = new StaffPolicy();

        $this->assertTrue($policy->setInitialPassword($staff));
    }

    /** @test */
    public function it_disallow_an_already_password_set_staff_to_set_a_new_password()
    {
        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('hasSetInitialPassword')->withNoArgs()->once()->andReturnTrue();

        $policy = new StaffPolicy();

        $this->assertFalse($policy->setInitialPassword($staff));
    }
}
