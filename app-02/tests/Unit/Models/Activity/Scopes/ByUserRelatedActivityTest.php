<?php

namespace Tests\Unit\Models\Activity\Scopes;

use App\Models\Activity;
use App\Models\Activity\Scopes\ByUserRelatedActivity;
use App\Models\RelatedActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByUserRelatedActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_registers_that_has_related_user_activity()
    {
        $user = User::factory()->create();
        Activity::factory()->count(10)->create();
        RelatedActivity::factory()->usingUser($user)->count(5)->create();

        $this->assertCount(5, Activity::scoped(new ByUserRelatedActivity($user))->get());
    }
}
