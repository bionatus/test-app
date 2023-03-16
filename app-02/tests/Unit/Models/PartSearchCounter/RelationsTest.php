<?php

namespace Tests\Unit\Models\PartSearchCounter;

use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PartSearchCounter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = PartSearchCounter::factory()->withStaff()->withUser()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_staff()
    {
        $related = $this->instance->staff()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_part_detail_counters()
    {
        PartDetailCounter::factory()->usingPartSearchCounter($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->partDetailCounters()->get();

        $this->assertCorrectRelation($related, PartDetailCounter::class);
    }
}
