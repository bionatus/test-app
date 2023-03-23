<?php

namespace Tests\Unit\Models\Casts;

use App\Casts\Money;
use App\Models\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(Money::class);

        $this->assertTrue($reflection->implementsInterface(CastsAttributes::class));
    }

    /** @test
     *
     * @param string $dollars
     * @param int    $expected
     *
     * @dataProvider setDataProvider
     */
    public function it_returns_money_converted_into_cents(
        string $dollars,
        int $expected
    ) {
        $fakeClass = Mockery::mock(Model::class);

        $money = new Money();
        $cents = $money->set($fakeClass, 'fake_key', $dollars, []);

        $this->assertSame($expected, $cents);
    }

    public function setDataProvider(): array
    {
        return [
            'With 2 decimals'  => ['12.34', 1234],
            'With 1 decimal'   => ['12.3', 1230],
            'Without decimals' => ['12', 1200],
        ];
    }

    /** @test
     *
     * @param int $databaseValue
     * @param     $expected
     *
     * @dataProvider getDataProvider
     */
    public function it_returns_money_converted_into_dollars(
        int $databaseValue,
        $expected
    ) {
        $fakeClass = Mockery::mock(Model::class);

        $money   = new Money();
        $dollars = $money->get($fakeClass, 'fake_key', $databaseValue, []);

        $this->assertSame($expected, $dollars);
    }

    public function getDataProvider(): array
    {
        return [
            'With 2 decimals'  => [1234, 12.34],
            'With 1 decimal'   => [1230, 12.3],
            'Without decimals' => [1200, 12],
        ];
    }
}
