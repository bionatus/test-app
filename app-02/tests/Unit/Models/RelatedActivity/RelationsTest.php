<?php

namespace Tests\Unit\Models\RelatedActivity;

use App\Models\Activity;
use App\Models\RelatedActivity;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property RelatedActivity $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = RelatedActivity::factory()->create();
    }

    /** @test */
    public function it_has_a_user()
    {
        $related = $this->instance->user;

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_an_activity()
    {
        $related = $this->instance->activity;

        $this->assertInstanceOf(Activity::class, $related);
    }
}
