<?php

namespace Tests\Unit\Models\Topic;

use App\Models\Subject;
use App\Models\Subtopic;
use App\Models\Topic;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Topic $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Topic::factory()->create();
    }

    /** @test */
    public function it_is_a_subject()
    {
        $related = $this->instance->subject()->first();

        $this->assertInstanceOf(Subject::class, $related);
    }

    /** @test */
    public function it_has_subtopics()
    {
        Subtopic::factory()->usingTopic($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->subtopics()->get();

        $this->assertCorrectRelation($related, Subtopic::class);
    }
}
