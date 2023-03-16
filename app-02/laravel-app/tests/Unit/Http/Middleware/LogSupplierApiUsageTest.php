<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Constants\Timezones;
use App\Http\Middleware\LogSupplierApiUsage;
use App\Models\ApiUsage;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Request;
use Tests\TestCase;

class LogSupplierApiUsageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_nothing_if_user_is_not_authenticated()
    {
        $request    = Request::instance();
        $middleware = App::make(LogSupplierApiUsage::class);

        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 0);
    }

    /** @test */
    public function it_logs_the_api_usage_for_the_supplier()
    {
        Carbon::setTestNow('2022-11-20 09:00:00');

        Auth::shouldUse('live');
        $request    = Request::instance();
        $middleware = App::make(LogSupplierApiUsage::class);
        $supplier   = Supplier::factory()->createQuietly();
        $staff      = Staff::factory()->usingSupplier($supplier)->create();
        $this->login($staff);

        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 1);
        $this->assertDatabaseHas(ApiUsage::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'date'        => Carbon::now()->startOfDay(),
        ]);
    }

    /** @test */
    public function it_only_logs_an_entry_for_a_supplier_each_day()
    {
        Carbon::setTestNow('2022-11-20 09:00:00');

        Auth::shouldUse('live');
        $request    = Request::instance();
        $middleware = App::make(LogSupplierApiUsage::class);
        $supplier   = Supplier::factory()->createQuietly();
        $staff      = Staff::factory()->usingSupplier($supplier)->create();
        ApiUsage::factory()->usingSupplier($supplier)->create(['date' => Carbon::now()->subDay()]);
        $this->login($staff);

        $middleware->handle($request, fn() => null);
        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 2);
        $this->assertDatabaseHas(ApiUsage::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'date'        => Carbon::now()->startOfDay(),
        ]);
    }

    /** @test
     * @throws \Throwable
     */
    public function it_logs_only_one_usage_if_different_staffs_from_the_same_supplier_uses_the_api()
    {
        Carbon::setTestNow('2022-11-20 09:00:00');

        Auth::shouldUse('live');
        $request    = Request::instance();
        $middleware = App::make(LogSupplierApiUsage::class);
        $supplier   = Supplier::factory()->createQuietly();
        $staff1     = Staff::factory()->usingSupplier($supplier)->create();
        $staff2     = Staff::factory()->usingSupplier($supplier)->create();

        $this->login($staff1);
        $middleware->handle($request, fn() => null);

        $this->login($staff2);
        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 1);
        $this->assertDatabaseHas(ApiUsage::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'date'        => Carbon::now()->startOfDay(),
        ]);
    }

    /** @test
     * @throws \Throwable
     */
    public function it_does_not_log_api_usage_if_it_is_disabled()
    {
        Config::set('api-usage.log_requests', false);

        Auth::shouldUse('live');
        $request    = Request::instance();
        $middleware = App::make(LogSupplierApiUsage::class);
        $supplier   = Supplier::factory()->createQuietly();
        $staff      = Staff::factory()->usingSupplier($supplier)->create();

        $this->login($staff);
        $middleware->handle($request, fn() => null);

        $this->assertDatabaseCount(ApiUsage::tableName(), 0);
        $this->assertDatabaseMissing(ApiUsage::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'date'        => Carbon::now()->startOfDay(),
        ]);
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

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $request    = Request::instance();
        $middleware = App::make(LogSupplierApiUsage::class);
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
