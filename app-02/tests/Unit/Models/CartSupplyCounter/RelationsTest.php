<?php

namespace Tests\Unit\Models\CartSupplyCounter;

use App\Models\CartSupplyCounter;
use App\Models\Supply;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CartSupplyCounter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CartSupplyCounter::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supply_category()
    {
        $related = $this->instance->supply()->first();

        $this->assertInstanceOf(Supply::class, $related);
    }
}
