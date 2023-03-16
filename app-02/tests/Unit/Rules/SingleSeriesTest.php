<?php

namespace Tests\Unit\Rules;

use App\Models\PlainTag;
use App\Models\Series;
use App\Rules\SingleSeries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleSeriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_true_if_elements_inside_are_not_an_array()
    {
        $rule = new SingleSeries();

        $this->assertTrue($rule->passes('', ['string']));
    }

    /** @test */
    public function it_returns_true_if_there_is_no_series()
    {
        $rule = new SingleSeries();

        $this->assertTrue($rule->passes('', [[PlainTag::MORPH_ALIAS, '1'], ['other', '1']]));
    }

    /** @test
     * @throws \Exception
     */
    public function it_returns_true_if_one_series_is_provided()
    {
        $series = Series::factory()->create();

        $rule = new SingleSeries();

        $passes = $rule->passes('', [$series->toTagType()->toArray()]);

        $this->assertTrue($passes);
    }

    /** @test
     * @throws \Exception
     */
    public function it_returns_false_if_two_series_are_provided()
    {
        $seriesOne = Series::factory()->create();
        $seriesTwo = Series::factory()->create();

        $rule = new SingleSeries();

        $passes = $rule->passes('', [
            $seriesOne->toTagType()->toArray(),
            $seriesTwo->toTagType()->toArray(),
        ]);

        $this->assertFalse($passes);
    }
}
