<?php

namespace Tests\Unit\Rules;

use App\Rules\ArrayStringAllInteger;
use Tests\TestCase;

class ArrayStringAllIntegerTest extends TestCase
{
    /**
     * @test
     * @dataProvider itemsProvider
     */
    public function it_returns_false_if_an_item_is_not_integer(string $items)
    {
        $rule = new ArrayStringAllInteger();

        $this->assertFalse($rule->passes('', $items));
    }

    public function itemsProvider(): array
    {
        return [
            ['a,2,3'],
            ['1,b,3'],
            ['1,2,c'],
            ['1,2,3.5'],
        ];
    }

    /** @test */
    public function it_returns_true_if_all_items_are_integers()
    {
        $rule = new ArrayStringAllInteger();

        $this->assertTrue($rule->passes('', implode(',', collect()->range(1, 101)->all())));
    }
}
