<?php

namespace Tests\Unit\Rules\Location;

use App\Rules\Location\Latitude;
use Tests\TestCase;

class LatitudeTest extends TestCase
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
        $rule = new Latitude();

        $this->assertSame($expected, $rule->passes('', $value));
    }

    public function provider()
    {
        return [
            ['invalid,', false],
            ['-91,', false],
            ['91,', false],
            ['-90,', true],
            ['0,', true],
            ['90,', true],
        ];
    }
}
