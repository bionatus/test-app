<?php

namespace Tests\Unit\Rules\ItemOrder;

use App\Models\Part;
use App\Models\Replacement;
use App\Rules\ItemOrder\ValidReplacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidReplacementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_if_value_is_a_replacement_not_related()
    {
        $part        = Part::factory()->create();
        $replacement = Replacement::factory()->create();
        $rule        = new ValidReplacement($part->item);

        $this->assertFalse($rule->passes('', $replacement->getRouteKey()));
    }

    /** @test */
    public function it_returns_true_if_value_is_a_replacement_of_the_item()
    {
        $part        = Part::factory()->create();
        $replacement = Replacement::factory()->usingPart($part)->create();
        $rule        = new ValidReplacement($part->item);

        $this->assertTrue($rule->passes('', $replacement->getRouteKey()));
    }

    /** @test */
    public function it_has_specific_error_message()
    {
        $part            = Part::factory()->create();
        $expectedMessage = 'This :attribute is not valid to replace the item';
        $rule            = new ValidReplacement($part->item);

        $this->assertEquals($expectedMessage, $rule->message());
    }
}
