<?php

namespace Tests\Unit\Models\PartDetailCounter;

use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PartDetailCounter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = PartDetailCounter::factory()->withStaff()->withUser()->withPartSearchCounter()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_staff()
    {
        $related = $this->instance->staff()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_part_search_counter()
    {
        $related = $this->instance->partSearchCounter()->first();

        $this->assertInstanceOf(PartSearchCounter::class, $related);
    }
}
