<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\ByEnabled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ByEnabledTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_disabled_at_null()
    {
        $count = 7;
        User::factory()->count(3)->create(['disabled_at' => Carbon::now()]);
        User::factory()->count($count)->create();

        $filtered = User::scoped(new ByEnabled())->count();

        $this->assertSame($count, $filtered);
    }
}
