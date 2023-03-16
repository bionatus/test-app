<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Rules\OrderDelivery\IsInStartWorkingHours;
use Tests\TestCase;

class IsInStartWorkingHoursTest extends TestCase
{
    /**
     * @test
     * @dataProvider hourProvider
     */
    public function it_returns_true_if_the_value_is_a_valid_start_time(bool $expected, string $startHour)
    {
        $workingHours = [
            'start_time' => ['09:00', '17:00'],
        ];

        $rule = new IsInStartWorkingHours($workingHours);

        $this->assertSame($expected, $rule->passes('', $startHour));
    }

    public function hourProvider(): array
    {
        return [
            [true, '09:00'],
            [true, '17:00'],
            [false, '12:00'],
        ];
    }

    /** @test */
    public function it_has_specific_message()
    {
        $rule = new IsInStartWorkingHours([]);

        $this->assertSame('The selected requested start time is invalid.', $rule->message());
    }
}
