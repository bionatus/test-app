<?php

namespace Tests\Unit\Models\InternalNotification;

use App\Models\InternalNotification;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property InternalNotification $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = InternalNotification::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
