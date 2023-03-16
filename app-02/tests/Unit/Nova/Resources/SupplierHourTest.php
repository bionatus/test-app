<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\SupplierHour;
use App\Nova\Resources;

class SupplierHourTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(SupplierHour::class, Resources\SupplierHour::$model);
    }

    /** @test */
    public function it_uses_the_day_as_title()
    {
        $this->assertSame('day', Resources\SupplierHour::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'day',
        ], Resources\SupplierHour::$search);
    }

    /** @test */
    public function it_should_not_be_displayed_in_navigation()
    {
        $this->assertFalse(Resources\SupplierHour::$displayInNavigation);
    }

    /** @test */
    public function it_as_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\SupplierHour::class, [
            'id',
            'day',
            'from',
            'to',
        ]);
    }
}
