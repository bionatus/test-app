<?php

namespace Tests\Unit\Models\LevelUser;

use App\Models\Level;
use App\Models\LevelUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property LevelUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = LevelUser::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_level()
    {
        $related = $this->instance->level()->first();

        $this->assertInstanceOf(Level::class, $related);
    }
}
