<?php

namespace Tests\Unit\Models\Level;

use App\Models\Level;
use App\Models\LevelUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Level $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Level::factory()->createQuietly();
    }

    /** @test */
    public function it_has_level_users()
    {
        LevelUser::factory()->usingLevel($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->levelUsers()->get();

        $this->assertCorrectRelation($related, LevelUser::class);
    }

    /** @test */
    public function it_has_users()
    {
        LevelUser::factory()->usingLevel($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }
}
