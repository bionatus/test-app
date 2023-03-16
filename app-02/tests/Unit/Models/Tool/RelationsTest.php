<?php

namespace Tests\Unit\Models\Tool;

use App\Models\Subject;
use App\Models\SubjectTool;
use App\Models\Tool;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Tool $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Tool::factory()->create();
    }

    /** @test */
    public function it_belongs_to_many_tools()
    {
        SubjectTool::factory()->usingTool($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->subjects()->get();

        $this->assertCorrectRelation($related, Subject::class);
    }

    /** @test */
    public function it_has_to_many_subject_tools()
    {
        SubjectTool::factory()->usingTool($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->subjectTools()->get();

        $this->assertCorrectRelation($related, SubjectTool::class);
    }
}
