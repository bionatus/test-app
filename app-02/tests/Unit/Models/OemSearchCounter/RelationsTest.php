<?php

namespace Tests\Unit\Models\OemSearchCounter;

use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OemSearchCounter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OemSearchCounter::factory()->withStaff()->withUser()->createQuietly();
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
    public function it_has_oem_detail_counters()
    {
        OemDetailCounter::factory()->usingOemSearchCounter($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemDetailCounters()->get();

        $this->assertCorrectRelation($related, OemDetailCounter::class);
    }
}
