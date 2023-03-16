<?php

namespace Tests\Unit\Policies\Nova\SupportCallCategory;

use App\Policies\Nova\SupportCallCategoryPolicy;
use App\User;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_support_call_category()
    {
        $policy = new SupportCallCategoryPolicy();

        $this->assertTrue($policy->create(new User()));
    }
}
