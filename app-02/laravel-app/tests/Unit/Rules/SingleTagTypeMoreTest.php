<?php

namespace Tests\Unit\Rules;

use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Series;
use App\Rules\SingleTagTypeMore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleTagTypeMoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_true_if_elements_inside_are_not_an_array()
    {
        $rule = new SingleTagTypeMore();

        $this->assertTrue($rule->passes('', ['string']));
    }

    /** @test */
    public function it_returns_true_if_there_is_no_type_more_tag()
    {
        $rule = new SingleTagTypeMore();

        $this->assertTrue($rule->passes('', [[Series::MORPH_ALIAS, '1'], [ModelType::MORPH_ALIAS, '1']]));
    }

    /** @test
     * @throws \Exception
     */
    public function it_returns_true_if_one_type_more_tag_is_provided()
    {
        $more = PlainTag::factory()->more()->create();

        $rule = new SingleTagTypeMore();

        $passes = $rule->passes('', [$more->toTagType()->toArray()]);

        $this->assertTrue($passes);
    }

    /** @test
     * @throws \Exception
     */
    public function it_returns_false_if_two_type_more_tag_are_provided()
    {
        $typeMoreOne = PlainTag::factory()->more()->create();
        $typeMoreTwo = PlainTag::factory()->more()->create();

        $rule = new SingleTagTypeMore();

        $passes = $rule->passes('', [
            $typeMoreOne->toTagType()->toArray(),
            $typeMoreTwo->toTagType()->toArray(),
        ]);

        $this->assertFalse($passes);
    }
}
