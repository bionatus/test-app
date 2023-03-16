<?php

namespace Tests\Unit\Models\PushNotificationToken;

use App\Models\Device;
use App\Models\PushNotificationToken;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PushNotificationToken $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = PushNotificationToken::factory()->create();
    }

    /** @test */
    public function it_belongs_to_device()
    {
        $related = $this->instance->device()->first();

        $this->assertInstanceOf(Device::class, $related);
    }
}
