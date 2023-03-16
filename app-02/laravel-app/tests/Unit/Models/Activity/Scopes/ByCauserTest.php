<?php

namespace Tests\Unit\Models\Activity\Scopes;

use App\Models\Activity;
use App\Models\Activity\Scopes\ByCauser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCauserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_causer()
    {
        $user = User::factory()->create();
        Activity::factory()->count(3)->create();
        Activity::factory()->usingCauser($user)->count(2)->create();

        $this->assertCount(2, Activity::scoped(new ByCauser($user))->get());
    }
}
