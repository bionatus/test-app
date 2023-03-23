<?php

namespace Tests\Unit\Models\VideoElapsedTime;

use App\Models\User;
use App\Models\VideoElapsedTime;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property VideoElapsedTime $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = VideoElapsedTime::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
