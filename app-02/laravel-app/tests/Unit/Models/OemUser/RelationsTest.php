<?php

namespace Tests\Unit\Models\OemUser;

use App\Models\Oem;
use App\Models\OemUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OemUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OemUser::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_oem()
    {
        $related = $this->instance->oem()->first();

        $this->assertInstanceOf(Oem::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
