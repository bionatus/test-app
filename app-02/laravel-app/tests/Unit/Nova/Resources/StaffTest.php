<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Staff;
use App\Nova\Resources;

class StaffTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Staff::class, Resources\Staff::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\Supplier::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'name',
            'email',
            'phone',
        ], Resources\Staff::$search);
    }

    /** @test */
    public function it_should_not_be_displayed_in_navigation()
    {
        $this->assertFalse(Resources\Staff::$displayInNavigation);
    }

    /** @test */
    public function it_creates_instances_of_staff_of_type_counter()
    {
        $newModel = Resources\Staff::newModel();
        $this->assertInstanceOf(Staff::class, $newModel);
        $this->assertSame('counter', $newModel->type);
        $this->assertSame('', $newModel->password);
    }
}
