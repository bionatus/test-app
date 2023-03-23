<?php

namespace Tests\Unit\Models\SubjectTool;

use App\Models\Subject;
use App\Models\SubjectTool;
use App\Models\Tool;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SubjectTool $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SubjectTool::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_subject()
    {
        $related = $this->instance->subject()->first();

        $this->assertInstanceOf(Subject::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_tool()
    {
        $related = $this->instance->tool()->first();

        $this->assertInstanceOf(Tool::class, $related);
    }
}
