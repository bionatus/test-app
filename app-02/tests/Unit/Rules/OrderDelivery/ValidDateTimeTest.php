<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Rules\OrderDelivery\ValidDateTime;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ValidDateTimeTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_date_time_provided_is_invalid()
    {
        Carbon::setTestNow('2022-11-12 17:31:00');

        $date = Carbon::now();
        $rule = new ValidDateTime($date->format('Y-m-d'), null);

        $this->assertFalse($rule->passes('attribute', 'not valid value'));
    }

    /** @test */
    public function it_returns_false_if_the_date_and_time_is_sooner_than_30_minutes_from_now()
    {
        Carbon::setTestNow('2022-11-12 17:31:00');

        $date = Carbon::now();
        $rule = new ValidDateTime($date->format('Y-m-d'), null);

        $this->assertFalse($rule->passes('attribute', Carbon::createFromTime(18)->format('H:i')));
    }

    /** @test */
    public function it_returns_true_if_the_date_and_time_is_past_30_minutes_from_now()
    {
        Carbon::setTestNow('2022-11-11 17:29:00');

        $date = Carbon::now();
        $rule = new ValidDateTime($date->format('Y-m-d'), null);

        $this->assertTrue($rule->passes('attribute', Carbon::createFromTime(18)->format('H:i')));
    }

    /** @test */
    public function it_has_custom_message()
    {
        Carbon::setTestNow('2022-11-11 17:29:00');

        $rule            = new ValidDateTime('', null);
        $minDate         = Carbon::now()->addMinutes(30)->format('Y-m-d h:iA');
        $expectedMessage = "The datetime should be after $minDate.";

        $this->assertEquals($expectedMessage, $rule->message());
    }

    /**
     * @test
     * @dataProvider timezoneProvider
     */
    public function it_depends_of_a_timezone(bool $expected, string $timezone)
    {
        Carbon::setTestNow('2022-11-11 23:29:00');

        $date = Carbon::now();
        $rule = new ValidDateTime($date->format('Y-m-d'), $timezone);

        $this->assertEquals($expected, $rule->passes('attribute', Carbon::createFromTime(18)->format('H:i')));
    }

    public function timezoneProvider(): array
    {
        return [
            [false, 'America/New_York'],
            [true, 'America/Los_Angeles'],
        ];
    }
}
