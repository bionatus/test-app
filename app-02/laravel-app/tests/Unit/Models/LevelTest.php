<?php

namespace Tests\Unit\Models;

use App\Models\Level;

class LevelTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Level::tableName(), [
            'id',
            'name',
            'slug',
            'from',
            'to',
            'coefficient',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_slug()
    {
        $level = Level::factory()->create(['slug' => 'something']);

        $this->assertEquals($level->slug, $level->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $level = Level::factory()->make(['slug' => null]);
        $level->save();

        $this->assertNotNull($level->slug);
    }

    /** @test */
    public function it_knows_if_its_the_lowest_level()
    {
        $level0 = Level::factory()->create([
            'slug' => 'level-0',
            'from' => 0,
            'to'   => 999,
        ]);
        $level1 = Level::factory()->create([
            'slug' => 'level-1',
            'from' => 1000,
            'to'   => null,
        ]);

        $this->assertTrue($level0->isLowestLevel());
        $this->assertFalse($level1->isLowestLevel());
    }

    /** @test */
    public function it_knows_if_its_the_highest_level()
    {
        $level0 = Level::factory()->create([
            'slug' => 'level-0',
            'from' => 0,
            'to'   => 999,
        ]);
        $level1 = Level::factory()->create([
            'slug' => 'level-1',
            'from' => 1000,
            'to'   => null,
        ]);

        $this->assertTrue($level1->isHighestLevel());
        $this->assertFalse($level0->isHighestLevel());
    }
}
