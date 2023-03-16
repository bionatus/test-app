<?php

namespace Tests\Unit\Policies\Nova\SupportCallCategory;

use App\Models\SupportCallCategory;
use App\Policies\Nova\SupportCallCategoryPolicy;
use App\User;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_allows_to_view_a_support_call_category()
    {
        $policy = new SupportCallCategoryPolicy();

        $this->assertTrue($policy->view(new User(), new SupportCallCategory()));
    }
}
