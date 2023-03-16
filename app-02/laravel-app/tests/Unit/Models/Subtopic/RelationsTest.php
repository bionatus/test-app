<?php

namespace Tests\Unit\Models\Subtopic;

use App\Models\Subject;
use App\Models\Subtopic;
use App\Models\Topic;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Subtopic $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Subtopic::factory()->create();
    }

    /** @test */
    public function it_is_a_subject()
    {
        $related = $this->instance->subject()->first();

        $this->assertInstanceOf(Subject::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_topic()
    {
        $related = $this->instance->topic()->first();

        $this->assertInstanceOf(Topic::class, $related);
    }
}
