<?php

namespace Tests\Unit\Models\OemDetailCounter;

use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OemDetailCounter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OemDetailCounter::factory()->withStaff()->withUser()->withOemSearchCounter()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_oem()
    {
        $related = $this->instance->oem()->first();

        $this->assertInstanceOf(Oem::class, $related);
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
    public function it_belongs_to_an_oem_search_counter()
    {
        $related = $this->instance->oemSearchCounter()->first();

        $this->assertInstanceOf(OemSearchCounter::class, $related);
    }
}
