<?php

namespace Tests\Unit\Models\Layout\Scopes;

use App\Models\Layout;
use App\Models\Layout\Scopes\ByVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByVersionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_lowers_or_equals_versions_by_major_version_number()
    {
        Layout::factory()->count(5)->sequence(fn($sequence) => [
            'version' => '1.' . $sequence->index . '.0',
        ])->create();
        Layout::factory()->count(5)->sequence(fn($sequence) => [
            'version' => '2.' . $sequence->index . '.0',
        ])->create();
        Layout::factory()->count(5)->sequence(fn($sequence) => [
            'version' => '3.' . $sequence->index . '.0',
        ])->create();

        $majorVersion = '2';
        $layouts      = Layout::scoped(new ByVersion($majorVersion . '.1.1'))->get();

        $this->assertCount(2, $layouts);
        $layouts->each(function($layout) use ($majorVersion) {
            $this->assertSame($majorVersion, substr($layout->version, 0, 1));
        });
        $layouts->each(function($layout) {
            $this->assertTrue($layout->version <= '2.1.1');
        });
    }
}
