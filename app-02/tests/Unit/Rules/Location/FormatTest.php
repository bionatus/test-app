<?php

namespace Tests\Unit\Rules\Location;

use App\Rules\Location\Format;
use Tests\TestCase;

class FormatTest extends TestCase
{
    /** @test
     *
     * @dataProvider provider
     *
     * @param $value
     * @param $expected
     */
    public function it_validates_properly($value, $expected)
    {
        $rule = new Format();

        $this->assertSame($expected, $rule->passes('', $value));
    }

    public function provider()
    {
        return [
            ['invalid', false],
            ['invalid,invalid,', false],
            ['[1]', false],
            [0, false],
            [10, false],
            [false, false],
            ['valid,valid', true],
        ];
    }
}
