<?php

namespace Tests\Unit\Models\Status;

use App\Models\Status;
use App\Models\Substatus;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Substatus $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Status::factory()->create();
    }

    /** @test */
    public function it_has_substatuses()
    {
        Substatus::factory()->usingStatus($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->substatuses()->get();

        $this->assertCorrectRelation($related, Substatus::class);
    }
}
