<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\SupplierCompany;
use App\Nova\Resources;

class SupplierCompanyTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(SupplierCompany::class, Resources\SupplierCompany::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\SupplierCompany::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
            'email',
        ], Resources\SupplierCompany::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\SupplierCompany::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\SupplierCompany::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\SupplierCompany::class, [
            'id',
            'name',
            'email',
        ]);
    }
}
