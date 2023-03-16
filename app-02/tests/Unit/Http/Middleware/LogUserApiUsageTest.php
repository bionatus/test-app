<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Constants\Timezones;
use App\Http\Middleware\LogUserApiUsage;
use App\Models\ApiUsage;
use App\Models\User;
use Auth;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Request;
use Tests\TestCase;

class LogUserApiUsageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_nothing_if_user_is_not_authenticated()
    {
        $request    = Request::instance();
        $middleware = App::make(LogUserApiUsage::class);

        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 0);
    }

    /**
     * @test
     * @dataProvider authProvider
     */
    public function it_logs_the_api_usage_for_a_user($guard)
    {
        Carbon::setTestNow('2022-11-20 09:00:00');

        Auth::shouldUse($guard);
        $request    = Request::instance();
        $middleware = App::make(LogUserApiUsage::class);
        $user       = User::factory()->create();
        $this->login($user);

        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 1);
        $this->assertDatabaseHas(ApiUsage::tableName(), [
            'user_id' => $user->getKey(),
            'date'    => Carbon::now()->startOfDay(),
        ]);
    }

    /**
     * @test
     * @dataProvider authProvider
     */
    public function it_only_logs_an_entry_for_a_user_each_day($guard)
    {
        Carbon::setTestNow('2022-11-20 09:00:00');

        Auth::shouldUse($guard);
        $request    = Request::instance();
        $middleware = App::make(LogUserApiUsage::class);
        $user       = User::factory()->create();
        ApiUsage::factory()->usingUser($user)->create(['date' => Carbon::now()->subDay()]);
        $this->login($user);

        $middleware->handle($request, fn() => null);
        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 2);
        $this->assertDatabaseHas(ApiUsage::tableName(), [
            'user_id' => $user->getKey(),
            'date'    => Carbon::now()->startOfDay(),
        ]);
    }

    /**
     * @test
     * @dataProvider authProvider
     */
    public function it_dont_logs_api_usage_if_it_is_disabled($guard)
    {
        Config::set('api-usage.log_requests', false);

        Auth::shouldUse($guard);
        $request    = Request::instance();
        $middleware = App::make(LogUserApiUsage::class);
        $user       = User::factory()->create();
        $this->login($user);

        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 0);
        $this->assertDatabaseMissing(ApiUsage::tableName(), [
            'user_id' => $user->getKey(),
            'date'    => Carbon::now()->startOfDay(),
        ]);
    }

    public function authProvider(): array
    {
        return [
            ['guard' => 'users'],
            ['guard' => 'latam'],
        ];
    }

    /** @test
     * @dataProvider dataProviderTimezone
     *
     * @throws \Throwable
     */
    public function it_logs_date_with_the_correct_timezone_depending_if_it_is_winter_time_or_summer_time(
        int $addedHours,
        bool $isSameDayInWinterTime,
        bool $isSameDayInSummerTime,
        bool $isWinterTime
    ) {
        Config::set('api-usage.tracking_timezone', Timezones::AMERICA_LOS_ANGELES);
        $utcDate     = $isWinterTime ? '2022-12-02' : '2022-07-02';
        $utcDateTime = Carbon::make("$utcDate 06:00:00")->setTimezone('UTC');
        Carbon::setTestNow($utcDateTime->addHours($addedHours));

        Auth::shouldUse('latam');
        $request    = Request::instance();
        $middleware = App::make(LogUserApiUsage::class);
        $user       = User::factory()->create();
        $this->login($user);

        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 1);
        $pacificDate = ApiUsage::first()->date;
        $this->assertSame($isWinterTime ? $isSameDayInWinterTime : $isSameDayInSummerTime,
            $utcDateTime->isSameDay($pacificDate));
    }

    public function dataProviderTimezone(): array
    {
        return [
            //addedHours, sameDayInWinterTime, sameDayInSummerTime, isWinterTime
            [0, false, false, true],
            [1, false, false, true],
            [2, true, true, true],
            [3, true, true, true],
            [0, false, false, false],
            [1, false, true, false],
            [2, true, true, false],
            [3, true, true, false],
        ];
    }
}
