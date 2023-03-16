<?php

namespace Tests\Unit\Models\CommunicationLog;

use App\Models\Communication;
use App\Models\CommunicationLog;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CommunicationLog $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CommunicationLog::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_communication()
    {
        $related = $this->instance->communication()->first();

        $this->assertInstanceOf(Communication::class, $related);
    }
}
