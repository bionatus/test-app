<?php

namespace Tests\Unit\Models\Flag;

use App\Models\Flag;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Flag $instance
 */
class RelationsTest extends RelationsTestCase
{
    /** @test */
    public function it_has_a_flaggable()
    {
        $user = User::factory()->create();
        $flag = Flag::factory()->usingModel($user)->create();

        $flaggable = $flag->flaggable()->first();

        $this->assertInstanceOf(User::class, $flaggable);
    }
}
