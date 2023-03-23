<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\ByStaff;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByStaffTest extends TestCase
{
    use RefreshDatabase;

    private Staff $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $supplier    = Supplier::factory()->createQuietly();
        $this->staff = Staff::factory()->usingSupplier($supplier)->createQuietly();
    }

    /** @test */
    public function it_filters_by_staff_on_setting_staff_model()
    {
        $expectedSettingStaff = SettingStaff::factory()->usingStaff($this->staff)->count(2)->createQuietly();
        SettingStaff::factory()->count(3)->createQuietly();

        $this->assertEquals($expectedSettingStaff->pluck('id'),
            SettingStaff::scoped(new ByStaff($this->staff))->pluck('id'));
    }
}
