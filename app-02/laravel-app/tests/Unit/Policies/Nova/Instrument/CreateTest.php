<?php

namespace Tests\Unit\Policies\Nova\Instrument;

use App\Policies\Nova\InstrumentPolicy;
use App\User;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_an_instrument()
    {
        $policy = new InstrumentPolicy();

        $this->assertTrue($policy->create(new User()));
    }
}
