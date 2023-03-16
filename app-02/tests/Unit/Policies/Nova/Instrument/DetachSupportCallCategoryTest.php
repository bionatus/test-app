<?php

namespace Tests\Unit\Policies\Nova\Instrument;

use App\Models\Instrument;
use App\Models\SupportCallCategory;
use App\Policies\Nova\InstrumentPolicy;
use App\User;
use Tests\TestCase;

class DetachSupportCallCategoryTest extends TestCase
{
    /** @test */
    public function it_disallows_to_detach_a_support_call_category()
    {
        $policy = new InstrumentPolicy();

        $this->assertFalse($policy->detachSupportCallCategory(new User(), new Instrument(), new SupportCallCategory()));
    }
}
