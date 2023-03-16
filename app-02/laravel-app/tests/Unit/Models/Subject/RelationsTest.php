<?php

namespace Tests\Unit\Models\Subject;

use App\Models\Session;
use App\Models\Subject;
use App\Models\SubjectTool;
use App\Models\Subtopic;
use App\Models\Ticket;
use App\Models\Tool;
use App\Models\Topic;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Subject $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Subject::factory()->create();
    }

    /** @test */
    public function it_is_a_topic()
    {
        Topic::factory()->usingSubject($this->instance)->create();

        $related = $this->instance->topic()->first();

        $this->assertInstanceOf(Topic::class, $related);
    }

    /** @test */
    public function it_is_a_subtopic()
    {
        Subtopic::factory()->usingSubject($this->instance)->create();

        $related = $this->instance->subtopic()->first();

        $this->assertInstanceOf(Subtopic::class, $related);
    }

    /** @test */
    public function it_has_tickets()
    {
        Ticket::factory()->usingSubject($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->tickets()->get();

        $this->assertCorrectRelation($related, Ticket::class);
    }

    /** @test */
    public function it_has_sessions()
    {
        Session::factory()->usingSubject($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->sessions()->get();

        $this->assertCorrectRelation($related, Session::class);
    }

    /** @test */
    public function it_belongs_to_many_tools()
    {
        SubjectTool::factory()->usingSubject($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->tools()->get();

        $this->assertCorrectRelation($related, Tool::class);
    }

    /** @test */
    public function it_has_subject_tools()
    {
        SubjectTool::factory()->usingSubject($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->subjectTools()->get();

        $this->assertCorrectRelation($related, SubjectTool::class);
    }
}
