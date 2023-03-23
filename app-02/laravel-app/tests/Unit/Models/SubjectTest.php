<?php

namespace Tests\Unit\Models;

use App\Models\Subject;

class SubjectTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Subject::tableName(), [
            'id',
            'slug',
            'name',
            'type',
        ]);
    }

    /** @test */
    public function it_knows_if_is_a_topic()
    {
        $topicSubject    = Subject::factory()->topic()->make();
        $subtopicSubject = Subject::factory()->subtopic()->make();

        $this->assertTrue($topicSubject->isTopic());
        $this->assertFalse($subtopicSubject->isTopic());
    }

    /** @test */
    public function it_knows_if_is_a_subtopic()
    {
        $topicSubject    = Subject::factory()->topic()->make();
        $subtopicSubject = Subject::factory()->subtopic()->make();

        $this->assertFalse($topicSubject->isSubtopic());
        $this->assertTrue($subtopicSubject->isSubtopic());
    }

    /** @test */
    public function it_uses_slug()
    {
        $subject = Subject::factory()->create(['slug' => 'something']);

        $this->assertEquals($subject->slug, $subject->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $subject = Subject::factory()->make(['slug' => null]);
        $subject->save();

        $this->assertNotNull($subject->slug);
    }
}
