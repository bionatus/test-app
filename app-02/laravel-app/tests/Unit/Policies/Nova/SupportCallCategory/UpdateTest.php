<?php

namespace Tests\Unit\Policies\Nova\SupportCallCategory;

use App\Models\SupportCallCategory;
use App\Policies\Nova\SupportCallCategoryPolicy;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_allows_to_update_a_support_call_category()
    {
        $policy = new SupportCallCategoryPolicy();

        $this->assertTrue($policy->update(new User(), new SupportCallCategory()));
    }
}
