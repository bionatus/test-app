<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Supply;
use App\Nova\Resources;

class SupplyTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Supply::class, Resources\Supply::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\Supply::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
        ], Resources\Supply::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\Supply::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\Supply::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\Supply::class, [
            'id',
            'name',
            'internal_name',
            'sort',
            'visible_at',
            'supplyCategory',
        ]);
    }
}
