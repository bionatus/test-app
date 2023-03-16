<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Instrument;
use App\Nova\Resources;

class InstrumentTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Instrument::class, Resources\Instrument::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\Instrument::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
        ], Resources\Instrument::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\Instrument::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\Instrument::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\Instrument::class, [
            'id',
            'name',
            'images',
            'supportCallCategories',
        ]);
    }
}
