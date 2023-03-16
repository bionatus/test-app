<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Constants\Timezones;
use App\Models\User;
use App\Models\User\Scopes\ByTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTimezoneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_timezone()
    {
        $expected = User::factory()->count(2)->create(['timezone' => Timezones::AMERICA_NEW_YORK]);
        User::factory()->count(3)->create(['timezone' => Timezones::AMERICA_LOS_ANGELES]);

        $filtered = User::scoped(new ByTimezone(Timezones::AMERICA_NEW_YORK))->get();

        $this->assertCount(2, $filtered);
        $filtered->each(function(User $user) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $user->getKey());
        });
    }

    /** @test */
    public function it_filters_by_timezone_with_null_value()
    {
        User::factory()->count(2)->create(['timezone' => null]);
        User::factory()->count(3)->create(['timezone' => Timezones::AMERICA_LOS_ANGELES]);

        $filtered = User::scoped(new ByTimezone(null))->get();

        $this->assertCount(2, $filtered);
    }
}
