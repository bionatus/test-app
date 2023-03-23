<?php

namespace Tests\Unit\Rules;

use App\Rules\ArrayStringMax;
use Tests\TestCase;

class ArrayStringMaxTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_the_string_has_more_than_max_items()
    {
        $rule = new ArrayStringMax(3);

        $this->assertFalse($rule->passes('', implode(',', collect()->range(1, 4)->all())));
    }

    /** @test */
    public function it_returns_true_if_the_string_has_less_or_equal_items()
    {
        $rule = new ArrayStringMax(3);

        $this->assertTrue($rule->passes('', implode(',', collect()->range(1, 3)->all())));
        $this->assertTrue($rule->passes('', implode(',', collect()->range(1, 2)->all())));
    }
}
