<?php

namespace Tests\Unit\Models\Device;

use App\Models\Device;
use App\Models\PushNotificationToken;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Device $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Device::factory()->create();
    }

    /** @test */
    public function it_has_a_push_notification_token()
    {
        PushNotificationToken::factory()->usingDevice($this->instance)->create();

        $related = $this->instance->pushNotificationToken()->first();

        $this->assertInstanceOf(PushNotificationToken::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
