<?php

namespace Tests\Unit\Types;

use App\Types\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    /**
     * @test
     * @dataProvider centsProvider
     */
    public function it_returns_a_dollar_representation_of_a_value($value, $expected)
    {
        $this->assertEquals($expected, Money::toDollars($value));
    }

    public function centsProvider(): array
    {
        return [
            'With 2 decimals'  => [1234, 12.34],
            'With 1 decimal'   => [1230, 12.3],
            'Without decimals' => [1200, 12],
            'Null'             => [null, 0],
        ];
    }

    /**
     * @test
     * @dataProvider dollarsProvider
     */
    public function it_returns_a_cent_representation_of_a_value($value, $expected)
    {
        $this->assertEquals($expected, Money::toCents($value));
    }

    public function dollarsProvider(): array
    {
        return [
            'With 3 decimals up'      => ['10.186', 1019],
            'With 3 decimals up 2'    => ['10.185', 1019],
            'With 3 decimals down'    => ['10.184', 1018],
            'With 2 decimals'         => ['0.1', 10],
            'With 2 decimals extra'   => ['5.1', 510],
            'With 2 decimals extra 2' => ['12.34', 1234],
            'With 1 decimal'          => ['12.3', 1230],
            'Without decimals'        => ['12', 1200],
            'Null'                    => [null, 0],
        ];
    }

    /**
     * @test
     * @dataProvider dollarsToFormatProvider
     */
    public function it_returns_a_formatted_dollar_value($value, $expected)
    {
        $this->assertEquals($expected, Money::formatDollars($value));
    }

    public function dollarsToFormatProvider(): array
    {
        return [
            'With 2 decimals'  => [10.15, '10.15'],
            'With 1 decimal'   => [10.1, '10.10'],
            'Without decimals' => [10, '10.00'],
        ];
    }
}
