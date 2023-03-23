<?php

namespace Tests\Unit\Models\Setting\Scopes;

use App\Models\Setting;
use App\Models\Setting\Scopes\ByGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByGroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_group()
    {
        $expectedSettings = Setting::factory()->groupNotification()->count(3)->create();
        Setting::factory()->groupAgent()->count(2)->create();

        $filtered = Setting::scoped(new ByGroup(Setting::GROUP_NOTIFICATION))->get();

        $this->assertEqualsCanonicalizing($expectedSettings->modelKeys(), $filtered->modelKeys());
    }
}
