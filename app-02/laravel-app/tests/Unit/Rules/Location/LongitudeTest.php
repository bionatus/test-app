<?php

namespace Tests\Unit\Rules\Location;

use App\Rules\Location\Longitude;
use Tests\TestCase;

class LongitudeTest extends TestCase
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
        $rule = new Longitude();

        $this->assertSame($expected, $rule->passes('', $value));
    }

    public function provider()
    {
        return [
            [',invalid', false],
            [',-181', false],
            [',181', false],
            [',-180', true],
            [',0', true],
            [',180', true],
        ];
    }
}
