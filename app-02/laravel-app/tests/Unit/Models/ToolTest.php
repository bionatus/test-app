<?php

namespace Tests\Unit\Models;

use App\Models\Tool;

class ToolTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Tool::tableName(), [
            'id',
            'slug',
            'name',
        ]);
    }

    /** @test */
    public function it_uses_slug()
    {
        $tool = Tool::factory()->create(['slug' => 'something']);

        $this->assertEquals($tool->slug, $tool->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $tool = Tool::factory()->make(['slug' => null]);
        $tool->save();

        $this->assertNotNull($tool->slug);
    }
}
