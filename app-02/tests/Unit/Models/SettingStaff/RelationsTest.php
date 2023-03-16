<?php

namespace Tests\Unit\Models\SettingStaff;

use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SettingStaff $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SettingStaff::factory()->usingStaff(Staff::factory()->createQuietly())->create();
    }

    /** @test */
    public function it_belongs_to_a_staff()
    {
        $related = $this->instance->staff()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_setting()
    {
        $related = $this->instance->setting()->first();

        $this->assertInstanceOf(Setting::class, $related);
    }
}
