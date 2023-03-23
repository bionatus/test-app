<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Rules\OrderDelivery\ValidEndTime;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ValidEndTimeTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_start_time_provided_is_invalid()
    {
        $rule = new ValidEndTime('not valid start time');
        $this->assertFalse($rule->passes('attribute', Carbon::createFromTime(18)->format('H:i')));
    }

    /** @test */
    public function it_returns_false_if_end_time_provided_makes_an_invalid_range()
    {
        $rule = new ValidEndTime(Carbon::createFromTime(12)->format('H:i'));
        $this->assertFalse($rule->passes('attribute', Carbon::createFromTime(14)->format('H:i')));
    }

    /** @test
     * @dataProvider rangeDataProvider
     */

    public function it_returns_true_if_start_time_and_end_time_provided_makes_a_valid_range($startTime, $endTime)
    {
        $rule = new ValidEndTime($startTime);
        $this->assertTrue($rule->passes('attribute', $endTime));
    }

    public function rangeDataProvider(): array
    {
        return [
            [Carbon::createFromTime(6)->format('H:i'), Carbon::createFromTime(9)->format('H:i')],
            [Carbon::createFromTime(9)->format('H:i'), Carbon::createFromTime(12)->format('H:i')],
            [Carbon::createFromTime(12)->format('H:i'), Carbon::createFromTime(15)->format('H:i')],
            [Carbon::createFromTime(15)->format('H:i'), Carbon::createFromTime(18)->format('H:i')],
        ];
    }

    /** @test */
    public function it_has_custom_message()
    {
        $rule            = new ValidEndTime('');
        $expectedMessage = "This range is not enabled.";

        $this->assertEquals($expectedMessage, $rule->message());
    }
}
