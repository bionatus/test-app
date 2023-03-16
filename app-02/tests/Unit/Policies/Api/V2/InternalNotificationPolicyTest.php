<?php

namespace Tests\Unit\Policies\Api\V2;

use App\Models\InternalNotification;
use App\Models\User;
use App\Policies\Api\V2\InternalNotificationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalNotificationPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_owner_to_mark_it_as_read()
    {
        $internalNotification = InternalNotification::factory()->create();

        $policy = new InternalNotificationPolicy();

        $this->assertTrue($policy->read($internalNotification->user, $internalNotification));
    }

    /** @test */
    public function it_disallow_another_user_to_mark_it_as_read()
    {
        $internalNotification = InternalNotification::factory()->create();

        $policy = new InternalNotificationPolicy();

        $this->assertFalse($policy->read(new User(), $internalNotification));
    }
}
