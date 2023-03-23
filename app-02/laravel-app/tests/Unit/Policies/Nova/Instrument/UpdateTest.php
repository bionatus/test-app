<?php

namespace Tests\Unit\Policies\Nova\Instrument;

use App\Models\Instrument;
use App\Policies\Nova\InstrumentPolicy;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_allows_to_update_an_instrument()
    {
        $policy = new InstrumentPolicy();

        $this->assertTrue($policy->update(new User(), new Instrument()));
    }
}
