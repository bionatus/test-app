<?php

namespace Tests\Unit\Rules;

use App\Rules\MoneyFormat;
use Tests\TestCase;

class MoneyFormatTest extends TestCase
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
        $rule = new MoneyFormat();

        $this->assertSame($expected, $rule->passes('', $value));
    }

    public function provider()
    {
        return [
            ['12.345', false],
            ['12', true],
            ['12.3', true],
            ['12.34', true],
        ];
    }
}
