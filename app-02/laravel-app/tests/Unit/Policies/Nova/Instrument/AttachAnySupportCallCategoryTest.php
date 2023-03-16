<?php

namespace Tests\Unit\Policies\Nova\Instrument;

use App\Models\Instrument;
use App\Policies\Nova\InstrumentPolicy;
use App\User;
use Tests\TestCase;

class AttachAnySupportCallCategoryTest extends TestCase
{
    /** @test */
    public function it_disallows_to_attach_a_support_call_category()
    {
        $policy = new InstrumentPolicy();

        $this->assertFalse($policy->attachAnySupportCallCategory(new User(), new Instrument()));
    }
}
