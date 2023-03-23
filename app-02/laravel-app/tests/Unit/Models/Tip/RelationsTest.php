<?php

namespace Tests\Unit\Models\Tip;

use App\Models\Part;
use App\Models\PartTip;
use App\Models\Tip;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Tip $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Tip::factory()->create();
    }

    /** @test */
    public function it_has_parts()
    {
        Part::factory()->usingTip($this->instance)->count(10)->create();

        $related = $this->instance->parts()->get();

        $this->assertCorrectRelation($related, Part::class);
    }
}
