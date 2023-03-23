<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Rules\OrderDelivery\AreInRangeOfWorkingHours;
use Tests\TestCase;

class AreInRangeOfWorkingHoursTest extends TestCase
{
    private array $validRanges;

    /**
     * @test
     * @dataProvider rangeProvider
     */
    public function it_returns_true_if_the_value_forms_a_valid_time_range_with_the_start_time(
        bool $expected,
        string $startTime,
        string $endTime
    ) {
        $rule = new AreInRangeOfWorkingHours($this->validRanges, $startTime);

        $this->assertSame($expected, $rule->passes('', $endTime));
    }

    public function rangeProvider(): array
    {
        return [
            [true, '09:00', '10:00'],
            [true, '16:00', '17:00'],
            [false, '12:00', '13:00'],
            [false, '09:00', '13:00'],
            [false, '12:00', '17:00'],
        ];
    }

    /** @test */
    public function it_has_custom_message()
    {
        $rule            = new AreInRangeOfWorkingHours([], '');
        $expectedMessage = "This range of hours is not enabled.";

        $this->assertEquals($expectedMessage, $rule->message());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->validRanges = [
            ['start' => '09:00', 'end' => '10:00'],
            ['start' => '16:00', 'end' => '17:00'],
        ];
    }
}
